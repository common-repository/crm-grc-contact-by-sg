<?php

namespace SGPLUGIN\Core;

/**
 * Class Core
 * @package SGPLUGIN\Core
 */
abstract class Core {

    /**
     * @var Core
     * @access private
     * @static
     */
    private static $_plugin = null;
    protected $pluginName;
    private $scripts = [];
    public $key_nonce = null;
    protected $cipher = 'AES-256-CBC';
    protected $key = 'IFZDxghtteqRL9p5iGT/UvSu4+19AfeoEUfoWGzURzE=';

    /**
     * Core constructor.
     * @param null $pluginName
     */
    public function __construct($pluginName = null) {
        $this->pluginName = $pluginName ?? basename(strtolower(str_replace( '\\', '/', get_called_class())));
        $this->key = base64_decode($this->key);
        $this->setup();
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments)
    {
        if(preg_match('/^template-/', $name)) {
            $this->displayTemplate($name);
        }
    }

    /**
     * Create and return a singleton of Core Class.
     *
     * @param void
     * @return Core
     */
    public static function getPlugin($pluginName = null):self {
        if(is_null(self::$_plugin)) {
            $class = get_called_class();
            self::$_plugin = new $class($pluginName);
        }
        return self::$_plugin;
    }

    /**
     * Create an custom option at the installation to define the state of the plugin.
     * The option value can be activated (1) or unactivated (0)
     */
    public function activation():void {
        if(get_option('activated_'.$this->pluginName, false) === false)
            add_option( 'activated_'.$this->pluginName, '1' );
        else
            update_option( 'activated_'.$this->pluginName, '1' );
    }

    /**
     * Create an custom option at the installation to define the state of the plugin.
     * The option value can be activated (1) or unactivated (0)
     */
    public function deactivation():void {
        if(get_option('activated_'.$this->pluginName, false) === false)
            add_option( 'activated_'.$this->pluginName, '0' );
        else
            update_option( 'activated_'.$this->pluginName, '0' );
    }

    /**
     * Setup all needed actions at start
     */
    protected function setup():void {
        register_activation_hook( WP_GRC_PLUGIN, [$this, 'activation'] );
        register_deactivation_hook( WP_GRC_PLUGIN, [$this, 'deactivation'] );
        add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 10, 1 );
    }

    /**
     * Set plugin's scripts in queue.
     * There can be a main script for all the plugin and script by page of plugin.
     *
     * @param string $hook_suffix
     */
    public function admin_enqueue_scripts(string $hook_suffix):void {
        if(false === preg_match("/{$this->pluginName}/", $hook_suffix))
            return;

        if(count($this->scripts) === 0)
            return;

        wp_enqueue_script( $this->pluginName.'-admin-main',
            $this->pluginUrl($this->scripts['main']),
            [], WP_GRC_VERSION, true
        );

        foreach($this->scripts as $key => $script) {
            if($key == 'main') continue;
            if (true == preg_match("/{$key}/", $hook_suffix)) {
                wp_enqueue_script($this->pluginName . '-admin-' . $key,
                    $this->pluginUrl($script),
                    [], WP_GRC_VERSION, true
                );
            }
        }
    }

    /**
     * Render template by menu.
     *
     * @param string $name
     */
    protected function displayTemplate(string $name):void {
        if(preg_match('/-index$/', $name))
            $template = 'index';
        elseif (preg_match('/-subindex$/', $name))
            $template = 'subindex';
        else
            $template = str_replace('template-', '', $name);

        $path = plugin_dir_path(WP_GRC_PLUGIN) . 'template/' . $template . '.php';

        if(file_exists($path))
            include $path;
    }


    /**
     * Method to easily create admin page
     *
     * @param array $params
     */
    public function createAdminPage(array $params = []):void {
        $defaultParams = [
            'page_title' => $this->pluginName . ' Page', // Title of the page
            'menu_title' => $this->pluginName . ' Plugin', // Text to show on the menu link
            'capability' => 'manage_options', // Capability requirement to see the link
            'menu_slug' => $this->pluginName.'-index', // The 'slug' - file to display when clicking the link
            'function' => null,
            'icon_url' => 'dashicons-admin-generic', //WP_GRC_PLUGIN_DIR . 'template/icon/icon.svg',
            //'position' => 0
        ];

        $params = array_merge($defaultParams, $params);
        if($params['function'] !== false)
            $params['function'] = [$this, 'template-'.$params['menu_slug']];

        add_action('admin_menu', function() use ($params) {
            add_menu_page(
                ...array_values($params)
            );
        });
    }

    /**
     * Method to easily create admin subpage
     *
     * @param array $params
     */
    public function createAdminSubpage(array $params = []):void {
        $defaultParams = [
            'parent_slug' => $this->pluginName.'-index',
            'page_title' => $this->pluginName . ' Subpage', // Title of the page
            'menu_title' => $this->pluginName . ' Subpage', // Text to show on the menu link
            'capability' => 'manage_options', // Capability requirement to see the link
            'menu_slug' => $this->pluginName.'-subindex', // The 'slug' - file to display when clicking the link
            'function' => null,
            'position' => 1
        ];

        $params = array_merge($defaultParams, $params);
        if($params['function'] !== false)
            $params['function'] = [$this, 'template-'.$params['menu_slug']];

        add_action('admin_menu', function() use ($params) {
            add_submenu_page(
                ...array_values($params)
            );
        });
    }

    /**
     * Store the script that will be used by the plugin.
     * The key should be 'main' to be used in global context.
     * The key should be the same of parent_slug value to be used in local context.
     *
     * @param string $path
     * @param string $key
     */
    public function setScript(string $path, string $key = 'main'):void {
        $this->scripts[$key] = $path;
    }

    /**
     * Massive storage scripts that will be used by the plugin.
     *
     * @param array $scripts
     */
    public function setScripts(array $scripts = []):void {
        $this->scripts += $scripts;
    }

    /**
     * Retrieve the plugin URL.
     *
     * @param string|null $path
     * @return string
     */
    public function pluginUrl(?string $path = ''):string {
        $url = plugins_url($path, WP_GRC_PLUGIN);

        if(is_ssl() && 'http:' == substr($url, 0, 5))
            $url = 'https:'.substr($url, 5);

        return $url;
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     */
    protected function encrypt($value, $serialize = true)
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $iv
        );

        if ($value === false) {
            return 'Could not encrypt the data.';
        }

        // Once we get the encrypted value we'll go ahead and base64_encode the input
        // vector and create the MAC for the encrypted value so we can then verify
        // its authenticity. Then, we'll JSON the data into the "payload" array.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Could not encrypt the data.';
        }

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param  mixed  $payload
     * @param  bool  $unserialize
     * @return string
     */
    protected function decrypt($payload, $unserialize = true)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = \openssl_decrypt(
            $payload['value'], $this->cipher, $this->key, 0, $iv
        );

        if ($decrypted === false) {
            return 'Could not decrypt the data.';
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string  $iv
     * @param  mixed  $value
     * @return string
     */
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->key);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string  $payload
     * @return array
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (! $this->validPayload($payload)) {
            return 'The payload is invalid.';
        }

        if (! $this->validMac($payload)) {
            return 'The MAC is invalid.';
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed  $payload
     * @return bool
     */
    protected function validPayload($payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
            strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array  $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        $calculated = $this->calculateMac($payload, $bytes = random_bytes(16));

        return hash_equals(
            hash_hmac('sha256', $payload['mac'], $bytes, true), $calculated
        );
    }

    /**
     * Calculate the hash of the given payload.
     *
     * @param  array  $payload
     * @param  string  $bytes
     * @return string
     */
    protected function calculateMac($payload, $bytes)
    {
        return hash_hmac(
            'sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true
        );
    }

    /**
     * Add or update post meta after checking if it already exists
     *
     * @param $key
     * @param $postID
     * @param $data
     */
    protected function set_post_meta($key, $postID, $data) {
        if(get_post_meta($postID, $key, true))
            update_post_meta($postID, $key, wp_slash($data));
        else
            add_post_meta($postID, $key, wp_slash($data));
    }
}