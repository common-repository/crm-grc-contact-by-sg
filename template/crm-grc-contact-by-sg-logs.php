<?php
/**
 * @return array
 */
function get_logs():array {
    global $wpdb;

    $rowOK = $wpdb->get_results(
        "SELECT 
                    * 
                FROM 
                    {$wpdb->prefix}crm_grc_contact_logs as log
                    JOIN 
                    {$wpdb->prefix}posts as post
                        ON  log.post_id = post.ID
                    JOIN {$wpdb->prefix}postmeta as meta
                        ON post.ID = meta.post_id
                WHERE 
                    log.status = 'OK' 
                    AND meta.meta_key = 'form_key'
                    AND log.sended_at >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
                ORDER BY log.sended_at DESC
                LIMIT 500"
    );

    return [
        'OK' => $rowOK
    ];
}

$logs = get_logs();
?>
<div class="wrap" id="crm-grc-contact-log-api">

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
        >CRM GRC Contact logs API</h1>
    </div>

    <hr class="wp-header-end">

    <?php if(count($logs['OK']) == 0): ?>
    <div>
        <p>
            Pas de logs de succès
        </p>
    </div>
    <?php else: ?>
    <table class="widefat fixed" cellspacing="0">
        <thead>
        <tr>
            <th id="columnname" class="manage-column column-columnname" scope="col">Clé de formulaire</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Status</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Réponse</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Envoyé le</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Données envoyées</th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <th class="manage-column column-columnname" scope="col"></th>
            <th class="manage-column column-columnname" scope="col"></th>
            <th class="manage-column column-columnname" scope="col"></th>
            <th class="manage-column column-columnname" scope="col"></th>
            <th class="manage-column column-columnname" scope="col"></th>
        </tr>
        </tfoot>

        <tbody>
        <?php $i=1; foreach ($logs['OK'] as $log) : $i++;?>
            <tr class="<?php echo ($i %2 == 0) ? 'alternate' : ''; ?>">
                <td class="column-columnname">
                    <?php echo esc_html__($log->meta_value); ?>
                </td>
                <td class="column-columnname">
                    <?php echo esc_html__($log->status); ?>
                </td>
                <td class="column-columnname">
                    <?php echo esc_html__($log->response); ?>
                </td>
                <td class="column-columnname">
                    <?php
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $log->sended_at, new DateTimeZone('UTC'));
                    $date->setTimezone(new DateTimeZone('Europe/Paris'));
                    echo $date->format('d/m/Y H:i');
                    ?>
                </td>
                <td class="column-columnname">
                    <pre style="width: 100%; height: 150px; overflow: scroll;">
                        <?php echo esc_html__(trim(print_r(json_decode($log->form_data, true), true)))
                        ; ?>
                    </pre>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>