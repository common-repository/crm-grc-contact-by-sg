<?php ?>
<!-- hack -->
<input type="hidden">
<input type="hidden">
<input type="hidden">
<!-- endhack -->
<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>
        <th id="columnname" class="manage-column column-columnname" scope="col">API Entreprise</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Nom du champ de formulaire</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Valeur par défaut</th>
    </tr>
    </thead>

    <tfoot>
    <tr>
        <th class="manage-column column-columnname" scope="col"></th>
        <th class="manage-column column-columnname" scope="col"></th>
        <th class="manage-column column-columnname" scope="col"></th>
    </tr>
    </tfoot>

    <tbody>
    <?php $i=1; foreach ($this::dictionary['company'] as $item) : $i++;?>
        <tr class="<?php echo ($i %2 == 0) ? 'alternate' : ''; ?>">
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo $item; ?>"
                        readonly
                />
            </td>
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo (!empty($$item[1])) ? esc_attr(stripslashes($$item[1])) : ''; ?>"
                />
            </td>
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo (!empty($$item[2])) ? esc_attr(stripslashes($$item[2])) : ''; ?>"
                />
            </td>
        </tr>
    <?php endforeach; ?>
    <tbody>
    <thead>
    <tr>
        <th id="columnname" class="manage-column column-columnname" scope="col">API Contact</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Nom du champ de formulaire</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Valeur par défaut</th>
    </tr>
    </thead>

    <tbody>
    <?php $i=1; foreach ($this::dictionary['contact'] as $item) : $i++;?>
        <tr class="<?php echo ($i %2 == 0) ? 'alternate' : ''; ?>">
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo $item; ?>"
                        readonly
                />
            </td>
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo (!empty($$item[1])) ? esc_attr(stripslashes($$item[1])) : ''; ?>"
                />
            </td>
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo (!empty($$item[2])) ? esc_attr(stripslashes($$item[2])) : ''; ?>"
                />
            </td>
        </tr>
    <?php endforeach; ?>
    <tbody>
    <thead>
    <tr>
        <th id="columnname" class="manage-column column-columnname" scope="col">API Champs personnalisés</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Nom des champs de formulaire séparés avec un pipe "|"</th>
        <th id="columnname" class="manage-column column-columnname" scope="col"></th>
    </tr>
    </thead>

    <tbody>
    <?php $i=1; foreach ($this::dictionary['custom'] as $item) : $i++;?>
        <tr class="<?php echo ($i %2 == 0) ? 'alternate' : ''; ?>">
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo $item; ?>"
                        readonly
                />
            </td>
            <td class="column-columnname">
                <input
                        class="widefat"
                        type="text"
                        name="grc[<?php echo $item; ?>][]"
                        value="<?php echo (!empty($$item[1])) ? esc_attr(stripslashes($$item[1])) : ''; ?>"
                />
            </td>
            <td class="column-columnname">
            </td>
        </tr>
    <?php endforeach; ?>
    <tbody>
</table>
