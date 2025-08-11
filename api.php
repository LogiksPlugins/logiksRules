<?php
if (!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("processLogiksRule")) {

    include __DIR__."/LogiksRulesEngine.inc";
    
    function processLogiksRuleByGroup($ruleGroup, $dataObj, $processLogs = false) {
        $ruleList = _db()->_selectQ("sys_logiksrules", "rule_code,title,category,descs,engine,fields,conditions,actions, edited_by, edited_on, created_by, created_on", [
                    "guid"=>$_SESSION['SESS_GUID'], 
                    "blocked"=>"false", 
                    "rule_group"=>$ruleGroup
                ])->_GET();
        if(!$ruleList) {
            if($processLogs)
                return ["data"=>$dataObj, "logs"=>[]];
            else
                return $dataObj;
        }
        
        $processLogs = [];
        $tempData = $dataObj;
        foreach($ruleList as $a=>$jsonRule) {
            $result = processLogiksRule($jsonRule, $tempData, false);
            $tempData = $result['processed_data'];

            $processLogs[$a] = $result;
        }
        
        if($processLogs)
            return ["data"=>$tempData, "logs"=>$processLogs];
        else
            return $tempData;
    }

    function processLogiksRuleByCode($ruleCode, $dataObj, $processLogs = true) {
        $data = _db()->_selectQ("sys_logiksrules", "rule_code,title,category,descs,engine,fields,conditions,actions, edited_by, edited_on, created_by, created_on", [
                    "guid"=>$_SESSION['SESS_GUID'], 
                    "blocked"=>"false", 
                    "rule_code"=>$ruleCode
                ])->_GET();
        if(!$data) {
            return $dataObj;
        }
        
        $jsonRule = $data[0];
        
        return processLogiksRule($jsonRule, $dataObj, $processLogs);
    }

    // Simple usage
    function processLogiksRule($jsonRule, $dataObj, $processLogs = true) {
        $ruleEngine = new RuleEngine();
        $result = $ruleEngine->process($jsonRule, $dataObj);

        // Access processed data
        // $processedData = $result['processed_data'];
        // $logs = $result['execution_logs'];

        if($processLogs)
            return $result;
        else
            return $result['processed_data'];
    }

    // Batch processing
    function processLogiksRuleBatch($jsonRule, $dataArray) {
        $ruleEngine = new RuleEngine();
        $results = [];
        
        foreach ($dataArray as $index => $data) {
            try {
                $result = $ruleEngine->process($jsonRule, $data);
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'original_data' => $data
                ];
            }
        }
        
        return $results;
    }
}
?>