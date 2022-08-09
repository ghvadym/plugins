<table class="form-table">
    <tbody>
        <tr>
            <th>
                <label for="fx_event__title">
                    <?php _e('Title', 'fxevents') ?>
                </label>
            </th>
            <td>
                <input type="text"
                       name="fx_event__title"
                       id="fx_event__title"
                       value="<?php echo $data['fx_event__title'] ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="fx_event__subtitle">
                    <?php _e('Subtitle', 'fxevents') ?>
                </label>
            </th>
            <td>
                <input type="text"
                       name="fx_event__subtitle"
                       id="fx_event__subtitle"
                       value="<?php echo $data['fx_event__subtitle'] ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="fx_event__date">
                    <?php _e('Date event', 'fxevents') ?>
                </label>
            </th>
            <td>
                <input type="date"
                       name="fx_event__date"
                       id="fx_event__date"
                       value="<?php echo $data['fx_event__date'] ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="fx_event__time">
                    <?php _e('Time event', 'fxevents') ?>
                </label>
            </th>
            <td>
                <input type="time"
                       name="fx_event__time"
                       id="fx_event__time"
                       value="<?php echo $data['fx_event__time'] ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="fx_event__content">
                    <?php _e('The content', 'fxevents') ?>
                </label>
            </th>
            <td>
                <?php
                wp_editor(
                    $data['fx_event__content'],
                    'fx_event__content',
                    [
                        'textarea_name' => 'fx_event__content',
                        'textarea_rows' => 15
                    ]
                ); ?>
            </td>
        </tr>
    </tbody>
</table>
