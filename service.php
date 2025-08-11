<?php
if(!defined('ROOT')) exit('No direct script access allowed');

handleActionMethodCalls();


function _service_editor() {
    if($_REQUEST['rulecode']==null) {
        echo "<h3 align=center><br><br>Rule Code Not Found</h3>";
        exit();
    }
    $data = _db()->_selectQ("sys_logiksrules", "rule_code,title,category,descs,engine,fields,conditions,actions, edited_by, edited_on, created_by, created_on", [
            "guid"=>$_SESSION['SESS_GUID'], 
            "blocked"=>"false", 
            "rule_code"=>$_REQUEST['rulecode']
        ])->_GET();
    if(!$data) {
        echo "<h3 align=center><br><br>Rule Defination Not Found</h3>";
        exit();
    }
    
    $ruleData = $data[0];
    include_once __DIR__."/pages/editor.php";
}
function _service_listRuleGroups(){
    $data = _db()->_selectQ("sys_logiksrules", "rule_group, title, category", ["blocked"=>"false"])->_groupBy("rule_group")->_orderBy("rule_group ASC, id asc")->_GET();

    return $data;
}
function _service_listRules(){
    $data = [];
    if(isset($_REQUEST['rule_group']) && strlen($_REQUEST['rule_group'])>0) {
        $data = _db()->_selectQ("sys_logiksrules", "rule_code, title, category, engine", ["blocked"=>"false", "rule_group"=>$_REQUEST['rule_group']])->_GET();
    } else {
        $data = _db()->_selectQ("sys_logiksrules", "rule_code, title, category, engine", ["blocked"=>"false"])->_GET();
    }

    return $data;
}

function _service_createRule() {
    if($_POST['title']==null) {
        return [
            "status"=>"error",
            "msg"=>"Mandatory Title Field is Missing"
        ];
    }
    if(!isset($_POST['rule_group'])) $_POST['rule_group'] = $_POST['title'];

    $ruleCode = _slugify($_POST['title']);
    $ruleGroup = _slugify($_POST['rule_group']);

    $ans = _db()->_insertQ1("sys_logiksrules", [
            "guid"=>$_SESSION['SESS_GUID'],
            "groupuid"=>$_SESSION['SESS_GROUP_NAME'],
            "company_id"=>$_SESSION['COMP_ID'],
            "title"=>$_POST['title'],
            "rule_group"=>$ruleGroup,
            "rule_code"=>$ruleCode,
            "category"=>"General",
            "descs"=>"",
            "fields"=>"{}",
            "conditions"=>"{}",
            "actions"=>"{}",
            "created_by"=>$_SESSION['SESS_USER_ID'],
            "created_on"=>date("Y-m-d H:i:s"),
            "edited_by"=>$_SESSION['SESS_USER_ID'],
            "edited_on"=>date("Y-m-d H:i:s")
        ])->_RUN();

    if($ans) {
        return [
            "status"=>"success",
            "rule_code"=> $ruleCode
        ];
    } else {
        return [
            "status"=>"error",
            "msg"=>"Error While Creating Rule."._db()->get_error(),
            "rule_code"=> $ruleCode
        ];
    }
}

function _service_deleteRule() {
    if($_POST['rulecode']==null) {
        return [
            "status"=>"error",
            "msg"=>"Rule Code Not Defined",
        ];
    }
    $ruleCode = $_POST['rulecode'];

    $ans = _db()->_updateQ("sys_logiksrules", [
            "blocked"=>"true",
            "edited_by"=>$_SESSION['SESS_USER_ID'],
            "edited_on"=>date("Y-m-d H:i:s")
        ], [
            "guid"=>$_SESSION['SESS_GUID'],
            "company_id"=>$_SESSION['COMP_ID'],
            "rule_code"=>$ruleCode,
        ])->_RUN();

    if($ans) {
        return [
            "status"=>"success",
            "rule_code"=> $ruleCode
        ];
    } else {
        return [
            "status"=>"error",
            "msg"=>"Error While Creating Rule."._db()->get_error(),
            "rule_code"=> $ruleCode
        ];
    }
}

function _service_saveRule() {
    if($_POST['rulecode']==null) {
        return [
            "status"=>"error",
            "msg"=>"Rule Code Not Defined",
        ];
    }
    $ruleCode = $_POST['rulecode'];
    $payload = json_decode($_POST['payload'], true);

    $ans = _db()->_updateQ("sys_logiksrules", [
            "title"=>$payload['title'],
            // "category"=>"General",
            // "descs"=>"",
            "fields"=>json_encode($payload['fields']),
            "conditions"=>json_encode($payload['conditions']),
            "actions"=>json_encode($payload['actions']),
            "blocked"=>($payload['enabled']?"false":"true"),
            "edited_by"=>$_SESSION['SESS_USER_ID'],
            "edited_on"=>date("Y-m-d H:i:s")
        ], [
            "guid"=>$_SESSION['SESS_GUID'],
            "company_id"=>$_SESSION['COMP_ID'],
            "rule_code"=>$ruleCode,
        ])->_RUN();

    if($ans) {
        return [
            "status"=>"success",
            "rule_code"=> $ruleCode
        ];
    } else {
        return [
            "status"=>"error",
            "msg"=>"Error While Creating Rule."._db()->get_error(),
            "rule_code"=> $ruleCode
        ];
    }
}

?>