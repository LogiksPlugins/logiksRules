<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadModule("pages");
// loadModuleLib("reports","api");

$slug = _slug("mod/reportName");


// if(!isset($slug['reportName']) || strlen($slug['reportName'])<1) $reportType = "main";
// else $reportType= $slug['reportName'];


//echo _js(["filesaver","html2canvas"]);

function pageSidebar() {
    $html = "<ul id='rulesListing' class='list-group'></ul>";
    return $html;
}

function pageContentArea() {
    //$html = file_get_contents(__DIR__."/ui.html");
    
    return "<div id='ruleEditorBox'><h4 align=center><br>Load Rule ...</h4></div>";//$html;
}

printPageComponent(false,[
    "toolbar"=>[
        "reloadPage"=>["icon"=>"<i class='fa fa-refresh'></i>"],
        ['type'=>"bar"],
        "createRule"=>["icon"=>"<i class='fa fa-plus'></i>","title"=>"New Rule"],

        ['type'=>"bar"],
        "saveRule"=>["icon"=>"<i class='fa fa-save'></i>","title"=>"Save Rule","class"=>"hidden onEditor"],
        "exportJSON"=>["icon"=>"<i class='fa fa-download'></i>","title"=>"Export Rule","class"=>"hidden onEditor"],
        ['type'=>"bar"],
        "deleteRule"=>["icon"=>"<i class='fa fa-trash clr_red'></i>","title"=>"&nbsp;&nbsp;Delete Rule","class"=>"hidden onEditor"],
    ],
    "sidebar"=>"pageSidebar",
    "contentArea"=>"pageContentArea"
  ]);
?>
<style>
body .pageComp.withFixedBar .pageCompContainer {
    padding-top: 40px !important;
}
.list-group-item {
    cursor: pointer;
}
#ruleEditorBox {
    overflow: hidden;
}
</style>
<script>
var current_rule_code = null;
var current_rule_group = null;
$(function() {
    $("#rulesListing").delegate(".list-group-item", "click", function(ele) {
        if($(this).data("group")!=null)
            loadRuleGroup($(this).data("group"));
        else
            loadEditor($(this).data("rulecode"));
    });

    loadRuleList();
});
function reloadPage() {
    window.location.reload();
}
function loadRuleList() {
    $("#toolbtn_reloadPage").html('<i class="fa fa-refresh"></i>');
    $("#rulesListing").html("<div align=center class='ajaxloading ajaxloading3'></div>");
    processAJAXQuery(_service("logiksRules", "listRuleGroups", "json"), function(data) {
        if(data.Data && data.Data.length>0) {
            $("#rulesListing").html("");

            $.each(data.Data, function(k, row) {
                $("#rulesListing").append(`<li class='list-group-item' data-group='${row.rule_group}'><i class='fas fa-chevron-right pull-right'></i>${row.title}</li>`);
            });
        } else {
            $("#rulesListing").html("<h5 align=center>No Rules Found</h5>");
        }
    },"json");
}
function loadRuleGroup(rule_group) {
    if(rule_group==null || rule_group.length<=0) {
        loadRuleList();
        return;
    }
    current_rule_group = rule_group;
    $("#rulesListing").html("<div align=center class='ajaxloading ajaxloading3'></div>");
    $("#ruleEditorBox").html("<h4 align=center><br>Load Rule ...</h4>");
    processAJAXQuery(_service("logiksRules", "listRules", "json")+"&rule_group="+rule_group, function(data) {
        if(data.Data && data.Data.length>0) {
            $("#rulesListing").html("");

            $("#toolbtn_reloadPage").html('<i class="fa fa-arrow-left"></i>');
            $.each(data.Data, function(k, row) {
                $("#rulesListing").append(`<li class='list-group-item' data-rulecode='${row.rule_code}'>${row.title}</li>`);
            });
        } else {
            $("#rulesListing").html("<h5 align=center>No Rules Found</h5>");
        }
    },"json");
}
function createRule() {
    lgksPrompt("New Name for the rule (Please make sure that a unique name is given.)?", "New Rule", function(ans) {
        if(ans && ans.length>0) {
            if(current_rule_group == null) current_rule_group = "";
            processAJAXPostQuery(_service("logiksRules", "createRule", "json"),"title="+ans+"&rule_group="+current_rule_group, function(data) {
                if(data.Data) {
                    if(data.Data.status=="success") {
                        loadRuleGroup(current_rule_group);

                        if(data.Data.rule_code.length>0) {
                            loadEditor(data.Data.rule_code);
                        }
                    } else {
                        if(!data.Data.msg) data.Data.msg = "Unknown Error Occured, try agian after sometime";

                        lgksAlert(data.Data.msg);
                    }
                } else {
                    $("#rulesListing").html("<h5 align=center>No Rules Found</h5>");
                }
            },"json");
        }
    });
}
function saveRule() {
    processAJAXPostQuery(_service("logiksRules", "saveRule", "json"),`rulecode=${current_rule_code}&payload=${JSON.stringify(generateJSON())}`, function(data) {
        if(data.Data && data.Data.status!=null) {
            if(data.Data.status=="success") {
                lgksToast("Succesully Updated the rule");
            } else {
                if(!data.Data.msg) data.Data.msg = "Unknown Error Occured, try agian after sometime";

                lgksAlert(data.Data.msg);
            }
        } else {
            lgksAlert("Timeout while saving the rule");
        }
    },"json");
}
function deleteRule() {
    lgksConfirm("Do you want to delete the Selected Rule, this can not be undone, and any existing system using this RULE, will loose access to this rule?", "Delete Rule", function(ans) {
        if(ans) {
            $("#ruleEditorBox").html("<h4 align=center><br>Load Rule ...</h4>");
            processAJAXPostQuery(_service("logiksRules", "deleteRule", "json"),`rulecode=${current_rule_code}`, function(data) {
                    if(data.Data && data.Data.status!=null) {
                        if(data.Data.status=="success") {
                            lgksToast("Succesully Deleted the rule");
                        } else {
                            if(!data.Data.msg) data.Data.msg = "Unknown Error Occured, try agian after sometime";

                            lgksAlert(data.Data.msg);
                        }

                        loadRuleList();
                    } else {
                        lgksAlert("Timeout while deleting the rule");
                    }
                },"json");
        }
    });
}
function loadEditor(rule_code) {
    current_rule_code = rule_code;
    $("#ruleEditorBox").html("<div align=center class='ajaxloading ajaxloading3'></div>");
    $("#ruleEditorBox").load(_service("logiksRules", "editor")+"&rulecode="+rule_code);
}
</script>