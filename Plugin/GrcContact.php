<?php

namespace SGPLUGIN;

use SGPLUGIN\Core\Core;
use SGPLUGIN\Services\GRCAPIService;

/**
 * Class GrcContact
 * @package SGPLUGIN
 */
class GrcContact extends Core
{
    const post_type = 'grc_form_api';
    const meta_key = '_form_key';
    const option_key = 'grc_account_key';
    const option_password = 'grc_password_api';
    const field_for_mapping = 'grc-form-code';
    const logTableName = 'crm_grc_contact_logs';
    const post_meta_data_key = '_grc_mapping_values';

    /**
     * Dictionary used to map form data and API
     */
    const dictionary = [
        'company' => [
            'follower_user_key',
            'group_id',
            'company',
            'number',
            'activity',
            'add_1',
            'add_2',
            'zip_code',
            'city',
            'country',
            'email',
            'phone',
            'fax',
            'comment',
            'origin',
            'site',
            'workforce',
            'turnover',
        ],
        'contact' => [
            'title',
            'firstname',
            'lastname',
            'contact_email',
            'contact_phone',
            'contact_cell_phone',
            'function',
        ],
        'custom' => [
            'custom_field_names',
            'custom_field_values',
        ]
    ];

    /**
     * Setup all needed actions at start
     */
    protected function setup():void {
        parent::setup();

        add_action( 'init', [$this, 'createPostType'] );
        add_action( 'add_meta_boxes', [$this, 'add_post_meta_boxes']);
        add_action( 'save_post', [$this, 'on_save_post'], 10, 2 );
        add_action( 'delete_post', [$this, 'on_delete_post'], 10, 2 );
        add_filter( 'manage_'.$this::post_type.'_posts_columns' , [$this,'custom_post_type_columns']);
        add_action( 'manage_'.$this::post_type.'_posts_custom_column' , [$this,'fill_custom_post_type_columns'], 10, 2 );
        add_action( 'init', [$this, 'recordCredential'] );
        add_action( 'init', [$this, 'captureSendingForm'] );

        /**
         * notice-error – will display the message with a red left border.
         * notice-warning– will display the message with a yellow/orange left border.
         * notice-success – will display the message with a green left border.
         * notice-info – will display the message with a blue left border.
         * optionally use is-dismissible to add a closing icon to your message via JavaScript. Its behavior, however, applies only on the current screen. It will not prevent a message from re-appearing once the page re-loads, or another page is loaded.
         */
        add_action( 'admin_notices', function ()
        {
            global $current_screen;
            if (preg_match("/{$current_screen->parent_base}/", 'crm-grc-contact-index'))
                $this->noticeForToken();
        });

        add_action( 'admin_enqueue_scripts', function ($hook) {
            wp_enqueue_style( 'crm-grc-contact', WP_GRC_PLUGIN_DIR.'/assets/css/plugin.css' ,false,'1.0.0','all');
        } );
    }

    /**
     * Capture submited form
     *
     * Check submited form of compatible plugins for forward data to the API
     */
    public function captureSendingForm():void {
        if(isset($_POST['sib_form_action']) && ('subscribe_form_submit' == $_POST['sib_form_action']))
            if(isset($_POST[$this::field_for_mapping]))
                $this->sendRequest(filter_var($_POST[$this::field_for_mapping], FILTER_SANITIZE_MAGIC_QUOTES));
    }

    /**
     * Forward the form data to the API.
     *
     * @param string $key
     */
    private function sendRequest($key):void {
        global $wpdb;

        $accountKey = get_option($this::option_key, false);
        $password = get_option($this::option_password, false);
        $password = ($password) ? $this->decrypt($password) : $password;

        if (!$accountKey || !$password)
            return;

        $post = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}postmeta ON ID = post_id WHERE meta_key = %s AND meta_value = %s",
                [$this::meta_key, $key]
            )
        );

        $mapping = get_post_meta($post->ID, $this::post_meta_data_key, true);

        if(empty($mapping))
            return;

        if(!$data = $this->formateDataForAPI($mapping))
            return;

        $response = (new GRCAPIService($accountKey, $password))->sendContact($data);

        if($response->getStatusCode() == 200) {
            $response = json_decode($response->getBody()->getContents());
            $status = $response->status;
            $message = (empty($response->data)) ? $response->msg : $response->data->company_id;
        } else {
            $status = 'ERR_'.$response->getStatusCode();
            $message = $response->getReasonPhrase();
        }

        $this->saveRequestAPIStatus([
            'post_id' => $post->ID,
            'status' => $status,
            'response' => $message,
            'form_data' => json_encode($data)
        ]);
    }


    /**
     * Store the response of API into the database.
     *
     * @param array $data
     */
    private function saveRequestAPIStatus($data):void {
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}".$this::logTableName,
            $data, ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Formate the data to forward to the API, use mapping method.
     *
     * @param array $mapping
     * @return array
     */
    private function formateDataForAPI($mapping):array {
        array_walk_recursive($mapping, function(&$item, $key) {
            $item = (!empty($item) && is_string($item)) ? base64_decode($item) : $item;
        });

        $data = [];
        foreach($mapping as $value) {
            $row = $this->formateRowForAPI($value);
            if(!empty($row)) {
                $data[$value[0]] = $row;
            } elseif (!empty($value[2])) {
                $data[$value[0]] = $value[2];
            }
        }

        $this->formateCustomRowForAPI($mapping, $data);

        return (count($data)) ? $data : [];
    }

    /**
     * Map the the form data with the requested API format for custom rows.
     *
     * @param array $mapping
     * @param array $data
     */
    private function formateCustomRowForAPI($mapping, &$data):void {
        if(empty($mapping['custom_field_names'][1]) || empty($mapping['custom_field_values'][1]))
            return;

        $names = explode('|', $mapping['custom_field_names'][1]);
        if(!count($names))
            return;

        $values = explode('|', $mapping['custom_field_values'][1]);
        if(!count($values))
            return;

        $customNames = [];
        $customValues = [];
        foreach($names as $key => $name)
            if(!empty($_POST[$values[$key]])) {
                $customNames[] = $name;
                $customValues[] = sanitize_textarea_field($_POST[$values[$key]]);
                $data[$name] = sanitize_textarea_field($_POST[$values[$key]]);
            }
        $data['custom_field_names'] = implode('|', $customNames);
        $data['custom_field_values'] = implode('|', $customValues);
    }

    /**
     * Map the the form data with the requested API format for standard rows.
     *
     * @param array $value
     * @return string
     */
    private function formateRowForAPI($value):string {
        $parts = explode(',', $value[1]);
        $rows = [];
        foreach ($parts as $row)
            if(!empty($_POST[$row]))
                $rows[] = sanitize_textarea_field($_POST[$row]);

        return implode(' ', $rows);
    }

    /**
     * Render a notice in the administration by token state.
     * The token state can be : missing or OK.
     */
    private function noticeForToken():void {
        if(!get_option($this::option_key, false) || !get_option($this::option_password, false))
            include plugin_dir_path(WP_GRC_PLUGIN) . 'template/part/crm-grc-contact-token-missing.php';
        else
            include plugin_dir_path(WP_GRC_PLUGIN) . 'template/part/crm-grc-contact-token-ok.php';
    }

    /**
     * Add custom column title to the post type table.
     *
     * @param array $columns
     * @return array|string[]
     */
    public function custom_post_type_columns($columns):array {
        return $columns + ['mapping_code' => 'Code de mapping'];
    }

    /**
     * Add custom column content to the post type table.
     *
     * @param string $column
     * @param int $post_id
     */
    public function fill_custom_post_type_columns($column, $post_id):void {
        if ($column == 'mapping_code') {
            $key = get_post_meta( $post_id, $this::meta_key, true );
            include plugin_dir_path(WP_GRC_PLUGIN) . 'template/part/crm-grc-contact-shortcode.php';
        }
    }

    /**
     * Store API credentials to the database
     */
    public function recordCredential():void {
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], basename(__FILE__)))
            return;
        if(empty($_POST['grc-action']) || $_POST['grc-action'] != 'record-credential')
            return;

        if(
            !preg_match('/^[a-zA-Z0-9_-]+$/', $_POST[$this::option_key]) ||
            !preg_match('/^[a-zA-Z0-9_-]+$/', $_POST[$this::option_password])
        ) {
            $_SESSION[WP_GRC_PLUGIN_NAME . '-state'] = ['error' => true, 'type' => 'BAD_CREDENTIAL'];
            return;
        }

        $key = sanitize_textarea_field($_POST[$this::option_key]);
        $pass = sanitize_textarea_field($_POST[$this::option_password]);

        if(get_option($this::option_key, false) === false)
            add_option($this::option_key, $key);
        else
            update_option($this::option_key, $key);
        if(get_option($this::option_password, false) === false)
            add_option($this::option_password, $this->encrypt($pass));
        else
            update_option($this::option_password, $this->encrypt($pass));
    }

    /**
     * Encode data mapping in post meta.
     *
     * @param $post_id
     * @return array
     */
    public function encode_mapping($post_id):void
    {
        if(isset($_POST['grc']) && is_array($_POST['grc'])) {
            array_walk_recursive($_POST['grc'], function(&$item, $key) {
                $item = (!empty($item)) ? base64_encode($item) : $item;
            });
            $data = $_POST['grc'];
        }
        $this->set_post_meta($this::post_meta_data_key, $post_id, $data);
    }

    /**
     * Hook used to generate token in relationship with the custom post type.
     *
     * @param int $post_id
     * @param array $post
     * @return int|null
     */
    public function on_save_post($post_id, $post) {
        /* Verify the nonce before proceeding. */
        if (!isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], basename(__FILE__)))
            return $post_id;
        /* Get the post type object. */
        $post_type = get_post_type_object($post->post_type);
        /* Check if the current user has permission to edit the post. */
        if (!current_user_can($post_type->cap->edit_post, $post_id))
            return $post_id;

        $this->encode_mapping($post_id);

        $meta_value = get_post_meta($post_id, $this::meta_key, true);
        if(empty($meta_value))
            add_post_meta($post_id, $this::meta_key, $this->randomToken(), true);
    }

    /**
     * Hook used to delete relationship token.
     *
     * @param int $post_id
     */
    public function on_delete_post($post_id):void {
        delete_post_meta( $post_id, $this::meta_key);
        delete_post_meta($post_id, $this::post_meta_data_key);
    }

    /**
     * Add meta boxe used to display mapping key information.
     */
    public function add_post_meta_boxes():void {
        add_meta_box(
            strtolower($this->pluginName).'-main',
            esc_html__( 'Mapping entre le formulaire et l\'API', WP_GRC_PLUGIN_NAME),
            [$this, 'display_meta_box'],
            $this::post_type,
            'normal'
        );

        add_meta_box(
            strtolower($this->pluginName).'-'.$this::meta_key,
            esc_html__( 'Clé du formulaire', WP_GRC_PLUGIN_NAME ),
            [$this, 'display_key_box'],
            $this::post_type,
            'side',
            'default'
        );
    }

    /**
     * @param array $post
     */
    public function display_key_box($post):void {
        include plugin_dir_path(WP_GRC_PLUGIN) . 'template/crm-grc-contact-key-box.php';
    }

    /**
     * @param array $post
     */
    public function display_meta_box($post):void {
        $post_meta = get_post_meta( $post->ID, $this::post_meta_data_key, true );
        $mapping = (empty($post_meta)) ? [] : $post_meta;

        array_walk_recursive($mapping, function(&$item, $key) {
            $item = (!empty($item) && is_string($item)) ? base64_decode($item) : $item;
        });
        extract($mapping);

        wp_nonce_field(basename(__FILE__), 'nonce');
        include plugin_dir_path(WP_GRC_PLUGIN) . 'template/crm-grc-contact-form.php';
    }

    /**
     * @override
     * @param string $name
     */
    protected function displayTemplate($name): void {
        $this->key_nonce = basename(__FILE__);
        parent::displayTemplate($name);
    }

    /**
     * Hook used to create a custom post type
     */
    public function createPostType():void {
        /**
         * Post Type: GRC Contact form APIs.
         */
        $labels = [
            'name' => __( 'Formulaire APIs' , WP_GRC_PLUGIN_NAME),
            'singular_name' => __( 'Formulaire API', WP_GRC_PLUGIN_NAME ),
            "menu_name" => __( "GRC Contact APIs", WP_GRC_PLUGIN_NAME ),
            "all_items" => __( "Toutes les APIs", WP_GRC_PLUGIN_NAME ),
            "add_new" => __( "Ajouter une nouvelle API", WP_GRC_PLUGIN_NAME ),
            "add_new_item" => __( "Ajouter une nouvelle API", WP_GRC_PLUGIN_NAME ),
            "edit_item" => __( "Modifier l'API", WP_GRC_PLUGIN_NAME ),
            "new_item" => __( "Nouvelle API", WP_GRC_PLUGIN_NAME ),
            "view_item" => __( "Voir l'API", WP_GRC_PLUGIN_NAME ),
            "view_items" => __( "Voir les APIs", WP_GRC_PLUGIN_NAME ),
            "search_items" => __( "Chercher une API", WP_GRC_PLUGIN_NAME ),
            "not_found" => __( "API non trouvée", WP_GRC_PLUGIN_NAME ),
            "not_found_in_trash" => __( "Aucune API trouvée", WP_GRC_PLUGIN_NAME ),
            "parent" => __( "API parent", WP_GRC_PLUGIN_NAME ),
            "featured_image" => __( "Image de mise en avant", WP_GRC_PLUGIN_NAME ),
            "set_featured_image" => __( "Définir l'image de mise en avant", WP_GRC_PLUGIN_NAME ),
            "remove_featured_image" => __( "Retirer l'image de mise en avant", WP_GRC_PLUGIN_NAME ),
            "use_featured_image" => __( "Utiliser l'image de mise en avant", WP_GRC_PLUGIN_NAME ),
            "archives" => __( "Archive API", WP_GRC_PLUGIN_NAME ),
            "insert_into_item" => __( "Insérer dans l'API", WP_GRC_PLUGIN_NAME ),
            "uploaded_to_this_item" => __( "Téléverser sur cette API", WP_GRC_PLUGIN_NAME ),
            "filter_items_list" => __( "Filtrer la liste des APIs", WP_GRC_PLUGIN_NAME ),
            "items_list_navigation" => __( "Navigation dans les APIs", WP_GRC_PLUGIN_NAME ),
            "items_list" => __( "Liste des APIs", WP_GRC_PLUGIN_NAME ),
            "attributes" => __( "Attributs des APIs", WP_GRC_PLUGIN_NAME ),
            "name_admin_bar" => __( "API", WP_GRC_PLUGIN_NAME ),
            "item_published" => __( "API publiée", WP_GRC_PLUGIN_NAME ),
            "item_published_privately" => __( "API publiée en privé", WP_GRC_PLUGIN_NAME ),
            "item_reverted_to_draft" => __( "API repassée en brouillon", WP_GRC_PLUGIN_NAME ),
            "item_scheduled" => __( "API planifiée", WP_GRC_PLUGIN_NAME ),
            "item_updated" => __( "API mise à jour", WP_GRC_PLUGIN_NAME ),
            "parent_item_colon" => __( "API parent", WP_GRC_PLUGIN_NAME ),
        ];

        $args = [
            "label" => __( "GRC Contact APIs", WP_GRC_PLUGIN_NAME ),
            "labels" => $labels,
            "description" => "",
            "public" => false,
            "publicly_queryable" => false,
            "show_ui" => true,
            "show_in_rest" => false,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => false,
            "show_in_nav_menus" => false,
            "delete_with_user" => false,
            "exclude_from_search" => true,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => [ "slug" => "grc_api", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title"],
        ];
        register_post_type(
            self::post_type,
            $args
        );
    }

    /**
     * Generate a random token used to link a form with a mapping API.
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    private function randomToken($length = 16):string {
        if(!isset($length) || intval($length) <= 8 ){
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    /**
     * Execute some commands at install
     * @override
     */
    public function activation():void {
        parent::activation();
        $this->createTableLogs();
    }

    /**
     * Execute some commands at uninstall
     * @override
     */
    public function deactivation():void {
        parent::deactivation();
        $this->dropTableLogs();
    }

    /**
     * Drop table log
     */
    private function dropTableLogs():void {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}".$this::logTableName."`;");
    }

    /**
     * Create table log
     */
    private function createTableLogs():void {
        global $wpdb;

        $wpdb->query
        (
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}".$this::logTableName."` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `post_id` INT NOT NULL DEFAULT '0',
                `status` VARCHAR(50) NOT NULL,
                `response` VARCHAR(255) NULL,
                `form_data` TEXT NULL DEFAULT NULL,
                `sended_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8mb4_unicode_ci'
            ;"
        );
    }
}
