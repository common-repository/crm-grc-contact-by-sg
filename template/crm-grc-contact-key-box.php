<p>
    <label for="smashing-post-class"><?php _e( "Clé de mapping", WP_GRC_PLUGIN_NAME ); ?></label>
    <br />
    <input class="widefat" type="text" name="<?php echo esc_html__($this::meta_key); ?>" id="<?php echo esc_html__($this::meta_key); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID,  $this::meta_key, true ) ); ?>" />
</p>