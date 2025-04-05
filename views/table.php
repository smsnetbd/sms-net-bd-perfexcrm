<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'hash',
    'testsms',
    'sms_from',
    'sms_to',
    'sms_message',
    'error',
    'error_message',
    'request_id',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().ALPHASMS_MODULE_NAME.'_sms';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join = [], $where = [], $additionalSelect = ['id'], $sGroupBy = '', $searchAs = []);

$output  = $result['output'];

$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $request_id = $aRow['request_id'];

    $actions = '';
    $actions .= '<div class="tw-inline-flex">';
    $actions .= '<a href="#'.ALPHASMS_MODULE_NAME.'_'.$aRow['id'].'" onclick="init_smsapi_item('.$aRow['id'].'); return false;" class="btn btn-default btn-icon btn-sm" data-toggle="tooltip" title="' . _l('view') . '"><i class="fa-eye fa-solid"></i></a>';

    if (staff_can('delete', ALPHASMS_MODULE_NAME)) {
        $actions .= ' <a href="#" onclick="smsapi_item_delete('.$aRow['id'].'); return false;" class="btn btn-danger btn-icon btn-sm _delete" data-toggle="tooltip" title="' . _l('delete') . '"><i class="fa fa-trash-alt"></i></a>';
    }
    $actions .= '</div>';

    $testsms = '<i class="fa fa-check '.($aRow['testsms'] == 1 ? 'text-success':'tw-text-black/25').'"></i>';
    $error = (isset($aRow['error']) && !is_null($aRow['error']) && $aRow['error'] > 0 ? '<i class="fa fa-exclamation-circle text-danger pointer" data-toggle="tooltip" title="'.$aRow['error_message'].'"></i>' : '<i class="fa fa-check-circle text-success"></i>');

    $message = $aRow['sms_message'];
    $message = '<div class="tw-truncate width200" data-toggle="tooltip" data-title="'.$message.'">'.$message.'</div>';

    $points = $aRow['ms_points'];

    $row[]              = $aRow['id'];
    $row[]              = $actions;
    $row[]              = $request_id;
    $row[]              = $testsms;
    $row[]              = $error;
    $row[]              = $aRow['sms_from'];
    $row[]              = $aRow['sms_to'];
    $row[]              = $message;
    $row[]              = date_create($aRow['created_at'])->format('d M, Y H:i:s');
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}