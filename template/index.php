<?php if(isset($_SESSION[WP_GRC_PLUGIN_NAME.'-state'])) : ?>
    <div class="notice notice-error">
        <p>
            <br>
            <strong>
                <?php _e( 'Le format des clés n\'est pas correct, il doit être constitué de lettre, de chiffre, tiret haut, tiret bas', WP_GRC_PLUGIN_NAME ); ?>
            </strong>
            <br>
            <br>
        </p>
    </div>
<?php unset($_SESSION[WP_GRC_PLUGIN_NAME.'-state']); endif; ?>
<div class="wrap" id="crm-grc-contact-form-config">
    <div style="text-align: center;
background: white;
border: 1px solid #ccd0d4;
box-shadow: 0 1px 1px rgba(0,0,0,.04);margin-bottom: 2rem;">
        <img src="<?php echo WP_GRC_PLUGIN_DIR_URL; ?>/assets/img/logo.png" alt="">
        <h1
                class="wp-heading-inline"
                style="
            display: block;
            text-align: center;
            font-size: 1.8rem;
            color: #0087be;
            margin-bottom: 2rem;
            margin-top: 0;"
        >Configuration</h1>
    </div>

    <hr class="wp-header-end">

    <form name="post" action="/wp-admin/admin.php?page=<?php echo WP_GRC_PLUGIN_NAME.'-index'; ?>" method="post">
        <?php wp_nonce_field($this->key_nonce, 'nonce'); ?>
        <input type="hidden" name="grc-action" value="record-credential">
        <table class="widefat fixed" cellspacing="0">
            <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col">Account key</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Password API</th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th class="manage-column column-columnname" scope="col"></th>
                <th class="manage-column column-columnname" scope="col" style="text-align:right">
                    <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Enregistrer">
                </th>
            </tr>
            </tfoot>

            <tbody>
                <tr>
                    <td class="column-columnname">
                        <input
                                class="widefat"
                                type="text"
                                name="<?php echo esc_html__($this::option_key); ?>"
                                value="<?php echo esc_html__(get_option($this::option_key)); ?>"

                        />
                    </td>
                    <td class="column-columnname">
                        <?php $password = get_option($this::option_password, false); ?>
                        <input
                                class="widefat"
                                type="text"
                                name="<?php echo esc_html__($this::option_password); ?>"
                                value="<?php echo $password ? esc_html__($this->decrypt($password)) : $password; ?>"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>