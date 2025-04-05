<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>


<div class="col-md-12 no-padding">
    <div class="panel_s">
        <div class="panel-body">
            <?php if (! $sms) {
            ?>
                <div class="alert alert-warning text-center tw-mb-0"><?php echo _l('smsapi_record_not_found'); ?></div>
            <?php
            } else {
            ?>
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-9 _buttons">
                        <label class="pull-right">
                            <a href="#" onclick="smsapi_item_delete(<?php echo $sms->id; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('delete'); ?>" class="btn btn-sm btn-danger btn-with-tooltip" data-placement="bottom"><i class="fa-regular fa-trash-alt"></i></a>
                        </label>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div id="itemData">
                <?php

                        $html = '';

                        // Ensure $sms is an array before proceeding
                        $sms = is_array($sms) ? $sms : (array) $sms;

                        unset($sms['updated_at']);

                        if (!empty($sms)) {

                            // Start building the table
                            $html .= '<div class="table-responsive">';
                            $html .= '<table class="table dataTable ' . ALPHASMS_MODULE_NAME . ' table-hover">';
                            $html .= '<thead><tr><th>' . _l(ALPHASMS_MODULE_NAME . '_table_key') . '</th>';
                            $html .= '<th>' . _l(ALPHASMS_MODULE_NAME . '_table_value') . '</th></tr></thead><tbody>';

                            // Loop through data and generate rows
                            foreach ($sms as $key => $value) {
                                $html .= '<tr>';
                                $html .= '<th class="tw-font-medium">' . _l(ALPHASMS_MODULE_NAME . '_' . $key) . '</th>';
                                $html .= '<td>';

                                // Proper PHP block for condition
                                if ($key == "testsms" && $value == 1) {

                                    $html .= '<i class="fa fa-check text-success"></i>';

                                } elseif($key == "testsms" && $value == 0) {

                                    $html .= '<i class="fa fa-check tw-text-black/25"></i>';

                                } else {

                                    $html .= htmlspecialchars($value ?? '---', ENT_QUOTES, 'UTF-8');
                                    
                                }

                                $html .= '</td>';
                                $html .= '</tr>';
                            }

                            $html .= '</tbody></table></div>';
                        }

                        // Output the HTML
                        echo $html;

                    ?>


                </div>

            <?php } ?>
        </div>
    </div>
</div>