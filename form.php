<?php

function build_rows( $edit = false ) {
    $rows      = "\n";
    $indent    = '                ';
    $tab       = '    ';

    foreach ($_POST['input_type'] as $key => $input_type) {
        $rows     .= $indent . sprintf( '<tr class="row-%s">', str_replace( '_', '-', $_POST['name'][$key] ) ) . "\n";
        $rows     .= $indent . $tab . "<th scope=\"row\">\n";
        $required = ( $_POST['required'][$key] == 'yes' ) ? ' required="required"' : '';

        if ( ! in_array( $input_type, array( 'checkbox', 'radio' ) ) ) {
            $rows .= $indent . $tab . $tab . sprintf( '<label for="%s"><?php _e( \'%s\', \'%s\' ); ?></label>', $_POST['name'][$key], $_POST['label'][$key], $_POST['textdomain'] ) . "\n";
        } else {
            $rows .= $indent . $tab . $tab . sprintf( '<?php _e( \'%s\', \'%s\' ); ?>', $_POST['label'][$key], $_POST['textdomain'] ) . "\n";
        }

        $rows .= $indent . $tab . "</th>\n";

        $rows .= $indent . $tab . "<td>\n";

        switch ($input_type) {
            case 'text':
                $value = '';

                if ( $edit ) {
                    $value = sprintf( '<?php echo esc_attr( $item->%s ); ?>', $_POST['name'][$key] );
                }

                $rows .= $indent . $tab . $tab . sprintf( '<input type="text" name="%1$s" id="%1$s" class="regular-text" placeholder="<?php echo esc_attr( \'%2$s\', \'%3$s\' ); ?>" value="%4$s"%5$s />', $_POST['name'][$key], $_POST['label'][$key], $_POST['textdomain'], $value, $required ) . "\n";
                break;

            case 'textarea':
                $value = '';

                if ( $edit ) {
                    $value = sprintf( '<?php echo esc_textarea( $item->%s ); ?>', $_POST['name'][$key] );
                }
                $rows .= $indent . $tab . $tab . sprintf( '<textarea name="%1$s" id="%1$s"placeholder="<?php echo esc_attr( \'%2$s\', \'%3$s\' ); ?>" rows="5" cols="30"%5$s>%4$s</textarea>', $_POST['name'][$key], $_POST['label'][$key], $_POST['textdomain'], $value, $required ) . "\n";
                break;

            case 'select':
                $rows .= $indent . $tab . $tab . sprintf( '<select name="%1$s" id="%1$s"%2$s>', $_POST['name'][$key], $required ) . "\n";

                $options = explode( "\n", $_POST['values'][ $key ] );
                if ( $options ) {
                    foreach ($options as $option) {
                        $option   = explode( ':', $option );
                        $selected = '';

                        if ( $edit ) {
                            $selected = sprintf( ' <?php selected( $item->%s, \'%s\' ); ?>', $_POST['name'][$key], $option[0] );
                        }

                        $rows .= $indent . $tab . $tab . $tab . sprintf( '<option value="%s"%s>%s</option>', $option[0], $selected, trim( $option[1] ) ) . "\n";
                    }
                }

                $rows .= $indent . $tab . $tab . "</select>\n";
                break;

            case 'checkbox':
                $checked = '';

                if ( $edit ) {
                    $checked = sprintf( ' <?php checked( $item->%s, \'on\' ); ?>', $_POST['name'][$key] );
                }

                $rows .= $indent . $tab . $tab . sprintf( '<label for="%1$s"><input type="checkbox" name="%1$s" id="%1$s" value="on"%4$s%5$s /> <?php _e( \'%2$s\', \'%3$s\' ); ?></label>', $_POST['name'][$key], $_POST['values'][$key], $_POST['textdomain'], $checked, $required ) . "\n";
                break;

            default:
                # code...
                break;
        }

        if ( ! empty( $_POST['help'][ $key ] ) ) {
            if ( $input_type == 'textarea' ) {
                $rows .= $indent . $tab . $tab . '<p class="description">' . $_POST['help'][$key] . "</p>\n";
            } else {
                $rows .= $indent . $tab . $tab . '<span class="description">' . $_POST['help'][$key] . "</span>\n";
            }
        }

        $rows .= $indent . $tab . "</td>\n";
        $rows .= $indent . "</tr>\n";
    }

    $rows .= '             ';

    return $rows;
}


include 'header.php'; ?>

<div class="container">
    <div class="row">

        <div class="col-md-12">

            <div class="page-header">
                <h1>Generate Form Table</h1>
            </div>

            <?php if ( isset( $_POST['submit'] ) ) {
                $form_code    = file_get_contents( 'templates/form.php' );
                $new_rows     = build_rows();
                $edit_rows    = build_rows( true );
                $search_array = array(
                    '%heading%',
                    '%textdomain%',
                    '%nonce%',
                    '%submit_name%',
                );

                $replace_array = array(
                    $_POST['heading'],
                    $_POST['textdomain'],
                    $_POST['nonce'],
                    $_POST['submit_name'],
                );

                $new_code  = $edit_code = str_replace( $search_array, $replace_array, $form_code );
                $new_code  = str_replace( array( '%rows%', '%submit_new_text%' ), array( $new_rows, $_POST['submit_new_text'] ), $new_code );
                $edit_code = str_replace( array( '%rows%', '%submit_new_text%' ), array( $edit_rows, $_POST['submit_edit_text'] ), $edit_code );
                ?>
                <p><strong>add-item.php</strong></p>
                <pre style="overflow-y: scroll;width:100%;"><?php echo htmlentities( $new_code ); ?></pre>

                <p><strong>edit-item.php</strong></p>
                <pre style="overflow-y: scroll;width:100%;"><?php echo htmlentities( $edit_code ); ?></pre>
            <?php } ?>

            <form class="form-horizontal" method="post">

                <div class="form-group">
                    <label class="col-md-4 control-label" for="heading">Heading Name</label>
                    <div class="col-md-4">
                        <input id="heading" name="heading" type="text" placeholder="Add New Item" class="form-control input-md" value="<?php echo isset( $_POST['heading' ] ) ? $_POST['heading'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="textdomain">Textdomain</label>
                    <div class="col-md-4">
                        <input id="textdomain" name="textdomain" type="text" placeholder="wedevs" class="form-control input-md"  value="<?php echo isset( $_POST['textdomain' ] ) ? $_POST['textdomain'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="nonce">Nonce Key</label>
                    <div class="col-md-4">
                        <input id="nonce" name="nonce" type="text" placeholder="" class="form-control input-md"  value="<?php echo isset( $_POST['nonce' ] ) ? $_POST['nonce'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="submit_new_text">New Submit Button Text</label>
                    <div class="col-md-4">
                        <input id="submit_new_text" name="submit_new_text" type="text" placeholder="Add New Transaction" class="form-control input-md"  value="<?php echo isset( $_POST['submit_new_text' ] ) ? $_POST['submit_new_text'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="submit_edit_text">New Submit Button Text</label>
                    <div class="col-md-4">
                        <input id="submit_edit_text" name="submit_edit_text" type="text" placeholder="Update Transaction" class="form-control input-md"  value="<?php echo isset( $_POST['submit_edit_text' ] ) ? $_POST['submit_edit_text'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="submit_name">Submit Name</label>
                    <div class="col-md-4">
                        <input id="submit_name" name="submit_name" type="text" placeholder="submit_transaction" class="form-control input-md"  value="<?php echo isset( $_POST['submit_name' ] ) ? $_POST['submit_name'] : ''; ?>">
                    </div>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Input Type</th>
                            <th>Input Name</th>
                            <th>Label</th>
                            <th>Placeholder</th>
                            <th>Values</th>
                            <th>Help</th>
                            <th>Required</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ( isset( $_POST['submit'] ) ) {
                            foreach ($_POST['input_type'] as $key => $input_type) {
                                ?>
                                <tr>
                                    <td>
                                        <select name="input_type[<?php echo $key; ?>]" class="form-control input-md">
                                            <option value="text" <?php echo $input_type == 'text' ? 'selected' : ''; ?>>Text</option>
                                            <option value="textarea" <?php echo $input_type == 'textarea' ? 'selected' : ''; ?>>Text Area</option>
                                            <option value="select" <?php echo $input_type == 'select' ? 'selected' : ''; ?>>Select Dropdown</option>
                                            <option value="checkbox" <?php echo $input_type == 'checkbox' ? 'selected' : ''; ?>>Checkbox</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input id="textdomain" name="name[<?php echo $key; ?>]" type="text" placeholder="Input Name" class="form-control input-md" value="<?php echo isset( $_POST['name' ][ $key ] ) ? $_POST['name'][ $key ] : ''; ?>">
                                    </td>
                                    <td>
                                        <input id="textdomain" name="label[<?php echo $key; ?>]" type="text" placeholder="Field Label" class="form-control input-md" value="<?php echo isset( $_POST['label' ][ $key ] ) ? $_POST['label'][ $key ] : ''; ?>">
                                    </td>
                                    <td>
                                        <input id="textdomain" name="table_value[<?php echo $key; ?>]" type="text" placeholder="Book Title" class="form-control input-md" value="<?php echo isset( $_POST['table_value' ][ $key ] ) ? $_POST['table_value'][ $key ] : ''; ?>">
                                    </td>
                                    <td>
                                        <textarea name="values[<?php echo $key; ?>]" id="values" cols="20" rows="3" class="form-control input-md" placeholder="key:value pair, one per line"><?php echo isset( $_POST['values' ][ $key ] ) ? $_POST['values'][ $key ] : ''; ?></textarea>
                                    </td>
                                    <td>
                                        <input id="textdomain" name="help[<?php echo $key; ?>]" type="text" placeholder="Help Text" class="form-control input-md"value="<?php echo isset( $_POST['help' ][ $key ] ) ? $_POST['help'][ $key ] : ''; ?>">
                                    </td>
                                    <td>
                                        <label for="required"><input name="required[<?php echo $key; ?>]" type="radio" <?php echo $_POST['required'][ $key ] == 'yes' ? 'checked' : ''; ?> value="yes"> Yes</label> &nbsp;
                                        <label for="required"><input name="required[<?php echo $key; ?>]" type="radio" <?php echo $_POST['required'][ $key ] == 'no' ? 'checked' : ''; ?> value="no"> No</label>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-success form-add-row">+</a>
                                        <a href="#" class="btn btn-sm btn-danger remove-row">-</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td>
                                    <select name="input_type[1]" class="form-control input-md">
                                        <option value="text">Text</option>
                                        <option value="textarea">Text Area</option>
                                        <option value="select">Select Dropdown</option>
                                        <option value="checkbox">Checkbox</option>
                                    </select>
                                </td>
                                <td>
                                    <input id="textdomain" name="name[1]" type="text" placeholder="Input Name" class="form-control input-md">
                                </td>
                                <td>
                                    <input id="textdomain" name="label[1]" type="text" placeholder="Field Label" class="form-control input-md">
                                </td>
                                <td>
                                    <input id="textdomain" name="table_value[1]" type="text" placeholder="Book Title" class="form-control input-md">
                                </td>
                                <td>
                                    <textarea name="values[1]" id="values" cols="20" rows="3" class="form-control input-md" placeholder="key:value pair, one per line"></textarea>
                                </td>
                                <td>
                                    <input id="textdomain" name="help[1]" type="text" placeholder="Help Text" class="form-control input-md">
                                </td>
                                <td>
                                    <label for="required"><input name="required[1]" type="radio" value="yes"> Yes</label> &nbsp;
                                    <label for="required"><input name="required[1]" type="radio" checked value="No"> No</label>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-success form-add-row">+</a>
                                    <a href="#" class="btn btn-sm btn-danger remove-row">-</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="submit"></label>
                    <div class="col-md-4">
                        <button id="submit" name="submit" class="btn btn-primary">Generate Code</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>