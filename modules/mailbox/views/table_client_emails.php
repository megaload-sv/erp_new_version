<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'mail_outbox.to',
    db_prefix() . 'mail_outbox.subject',
    db_prefix() . 'mail_outbox.body',
    db_prefix() . 'mail_tags.name as tag_name',
    db_prefix() . 'emailtemplates.name as template_name',
    db_prefix() . 'mail_outbox.scheduled_at',
    db_prefix() . 'mail_outbox.date_sent'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mail_outbox';

$join = [
    'LEFT JOIN ' . db_prefix() . 'mail_tags ON ' . db_prefix() . 'mail_tags.id = ' . db_prefix() . 'mail_outbox.tagid',
    'LEFT JOIN ' . db_prefix() . 'emailtemplates ON ' . db_prefix() . 'emailtemplates.emailtemplateid = ' . db_prefix() . 'mail_outbox.templateid',
    'LEFT JOIN ' . db_prefix() . 'mail_clients ON ' . db_prefix() . 'mail_clients.outbox_id = ' . db_prefix() . 'mail_outbox.id',
];
$where = [];
array_push($where, 'AND trash = 0');
array_push($where, ' AND draft = 0');
if ($client_id) {
    array_push($where, ' AND ' . db_prefix() . 'mail_clients.client_id = ' . $client_id);
}
$group_by = ' GROUP BY ' . db_prefix() . 'mail_outbox.id';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'mail_outbox.id',
    db_prefix() . 'mail_outbox.stared',
    db_prefix() . 'mail_outbox.important',
    db_prefix() . 'mail_outbox.has_attachment',
    db_prefix() . 'mail_outbox.to',
    db_prefix() . 'mail_outbox.subject',
    db_prefix() . 'mail_outbox.body',
    db_prefix() . 'mail_tags.color as tag_color',
    db_prefix() . 'mail_outbox.scheduled_at',
    db_prefix() . 'mail_outbox.date_sent'
], $group_by, [3]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $starred = "fa-star";
    $msg_starred = _l('mailbox_add_star');
    $important = "fa-bookmark";
    $msg_important = _l('mailbox_mark_as_important');
    if ($aRow['stared'] == 1) {
        $starred = "fa-star orange";
        $msg_starred = _l('mailbox_remove_star');
    }
    if ($aRow['important'] == 1) {
        $important = "fa fa-bookmark green";
        $msg_important = _l('mailbox_mark_as_not_important');    
    }
    $has_attachment = "";
    if ($aRow['has_attachment'] > 0) {
        $has_attachment = '<i class="fa fa-paperclip pull-right" data-toggle="tooltip" title="'._l('mailbox_file_attachment').'" data-original-title="fa-paperclip"></i>';
    }

    $row[] = '<a class="btn btnIcon" data-toggle="tooltip" title="" data-original-title="'. _l('mailbox_delete').'" onclick="unassgin_customer('.$client_id.','.$aRow['id'].',\'outbox\');"><i class="fa fa-trash grey"></i></a>';

    $row[] = $content.'<span class="'.$read.'">'._l('mailbox_outbox').'</span></a>';
    $content = '<a href="'.admin_url().'mailbox/outbox/'.$aRow['id'].'">';
    $row[] = $content.'<span class="label" style="color: #fff; background: '.$aRow['tag_color'].'">'.$aRow['tag_name'].'</span></a>';
    $row[] = $content.'<span>'.$aRow[db_prefix() . 'mail_outbox.to'].'</span></a>';
    $row[] = $content.'<span>'.$aRow['subject'].($has_attachment?' - </span>'.$has_attachment:'').'</a>';
    $row[] = $content.text_limiter(clear_textarea_breaks($aRow['body']),10,'...').'</a>';
    $row[] = $content.'<span>'.$aRow['template_name'].'</span></a>';
    $row[] = $content.'<span>'._dt($aRow['date_sent']).'</span></a>';

    $output['aaData'][] = $row;
}

// Email Inbox
$aColumns = [
    db_prefix() . 'mail_tags.name as tag_name',
    db_prefix() . 'mail_inbox.sender_name',
    db_prefix() . 'mail_inbox.subject',
    db_prefix() . 'mail_inbox.body',
    db_prefix() . 'emailtemplates.name as template_name',
    db_prefix() . 'mail_inbox.date_received'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mail_inbox';

$join = [
    'LEFT JOIN ' . db_prefix() . 'mail_tags ON ' . db_prefix() . 'mail_tags.id = ' . db_prefix() . 'mail_inbox.tagid',
    'LEFT JOIN ' . db_prefix() . 'emailtemplates ON ' . db_prefix() . 'emailtemplates.emailtemplateid = ' . db_prefix() . 'mail_inbox.templateid',
    'LEFT JOIN ' . db_prefix() . 'mail_clients ON ' . db_prefix() . 'mail_clients.inbox_id = ' . db_prefix() . 'mail_inbox.id',
];
$where = [];
if ($client_id) {
    array_push($where, ' AND ' . db_prefix() . 'mail_clients.client_id = ' . $client_id);
}
array_push($where, ' AND trash = 0');
$group_by = ' GROUP BY ' . db_prefix() . 'mail_inbox.id';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'mail_inbox.id',
    db_prefix() . 'mail_inbox.stared',
    db_prefix() . 'mail_inbox.important',
    db_prefix() . 'mail_inbox.has_attachment',
    db_prefix() . 'mail_inbox.sender_name',
    db_prefix() . 'mail_inbox.subject',
    db_prefix() . 'mail_inbox.body',
    db_prefix() . 'mail_tags.color as tag_color',
    db_prefix() . 'mail_inbox.read',
    db_prefix() . 'mail_inbox.date_received'
], $group_by, [3]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $read = "bold";
    if ($aRow['read'] == 1) {
        $read = "";
    }
    $starred = "fa-star";
    $msg_starred = _l('mailbox_add_star');
    $important = "fa-bookmark";
    $msg_important = _l('mailbox_mark_as_important');
    if ($aRow['stared'] == 1) {
        $starred = "fa-star orange";
        $msg_starred = _l('mailbox_remove_star');
    }
    if ($aRow['important'] == 1) {
        $important = "fa fa-bookmark green";
        $msg_important = _l('mailbox_mark_as_not_important');
        
    }
    $has_attachment = "";
    if ($aRow['has_attachment'] > 0) {
        $has_attachment = '<i class="fa fa-paperclip pull-right" data-toggle="tooltip" title="'._l('mailbox_file_attachment').'" data-original-title="fa-paperclip"></i>';
    }

    $row[] = '<a class="btn btnIcon" data-toggle="tooltip" title="" data-original-title="'. _l('mailbox_delete').'" onclick="unassgin_customer('.$client_id.','.$aRow['id'].',\'inbox\');"><i class="fa fa-trash grey"></i></a>';

    $content = '<a href="'.admin_url().'mailbox/inbox/'.$aRow['id'].'">';
    $row[] = $content.'<span class="'.$read.'">'._l('mailbox_inbox').'</span></a>';
    $row[] = $content.'<span class="label" style="color: #fff; background: '.$aRow['tag_color'].';">'.$aRow['tag_name'].'</span></a>';
    $row[] = $content.'<span class="'.$read.'">'.$aRow['sender_name'].'</span></a>';
    $row[] = $content.'<span class="'.$read.'">'.$aRow['subject'].($has_attachment ? ' - </span>'.$has_attachment : '').'</a>';
    $row[] = $content.text_limiter(clear_textarea_breaks($aRow['body']),10,'...').'</a>';
    $row[] = $content.'<span>'.$aRow['template_name'].'</span></a>';
    $row[] = $content.'<span class="'.$read.'">'._dt($aRow['date_received']).'</span></a>';

    $output['aaData'][] = $row;
}