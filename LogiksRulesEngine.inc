<?php
/*
 * This class contains the Engine Code for processing LogiksRules -
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');


class LogiksRulesEngine
{
    private $rule;
    private $data;
    private $processedData;
    private $logs = [];

    /**
     * Process data using the provided rule
     * 
     * @param array|string $rule - Rule array or JSON string
     * @param array $data - Input data to process
     * @return array - Processed data with execution logs
     */
    public function process($rule, $data)
    {
        // Parse rule if it's JSON string
        if (is_string($rule)) {
            $this->rule = json_decode($rule, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON rule format');
            }
        } else {
            $this->rule = $rule;
        }

        // Initialize data
        $this->data = $data;
        $this->processedData = $data; // Copy original data
        $this->logs = [];

        // Validate rule structure
        $this->validateRule();

        // Check if rule is enabled
        if (isset($this->rule['enabled']) && !$this->rule['enabled']) {
            $this->log('Rule is disabled, skipping execution');
            return $this->getResult();
        }

        // Evaluate conditions
        $conditionsResult = $this->evaluateConditions($this->rule['conditions']);
        
        $this->log("Rule '{$this->rule['rule_code']}' conditions evaluated to: " . ($conditionsResult ? 'TRUE' : 'FALSE'));

        // Execute actions if conditions are met
        if ($conditionsResult) {
            $this->executeActions();
        }

        return $this->getResult();
    }

    /**
     * Recursively evaluate nested conditions
     * 
     * @param array $conditions - Conditions structure
     * @return bool - Evaluation result
     */
    private function evaluateConditions($conditions)
    {
        if (!isset($conditions['operator']) || !isset($conditions['conditions'])) {
            return true; // Empty conditions default to true
        }

        $operator = strtoupper($conditions['operator']);
        $conditionsList = $conditions['conditions'];
        $results = [];

        foreach ($conditionsList as $condition) {
            if (isset($condition['field'])) {
                // Simple condition
                $result = $this->evaluateSimpleCondition($condition);
                $results[] = $result;
                $this->log("Condition {$condition['field']} {$condition['operator']} {$condition['value']}: " . ($result ? 'TRUE' : 'FALSE'));
            } else {
                // Nested condition group
                $result = $this->evaluateConditions($condition);
                $results[] = $result;
                $this->log("Nested group with operator {$condition['operator']}: " . ($result ? 'TRUE' : 'FALSE'));
            }
        }

        // Apply logical operator
        if ($operator === 'OR') {
            return in_array(true, $results);
        } else { // Default to AND
            return !in_array(false, $results);
        }
    }

    /**
     * Evaluate a single condition
     * 
     * @param array $condition - Single condition
     * @return bool - Evaluation result
     */
    private function evaluateSimpleCondition($condition)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $expectedValue = $condition['value'];

        // Get actual value from data using dot notation
        $actualValue = $this->getValueFromPath($this->data, $field);

        switch ($operator) {
            case 'equals':
                return $actualValue == $expectedValue;
            
            case 'not_equals':
                return $actualValue != $expectedValue;
            
            case 'greater_than':
                return is_numeric($actualValue) && is_numeric($expectedValue) && 
                       floatval($actualValue) > floatval($expectedValue);
            
            case 'less_than':
                return is_numeric($actualValue) && is_numeric($expectedValue) && 
                       floatval($actualValue) < floatval($expectedValue);
            
            case 'greater_than_or_equal':
                return is_numeric($actualValue) && is_numeric($expectedValue) && 
                       floatval($actualValue) >= floatval($expectedValue);
            
            case 'less_than_or_equal':
                return is_numeric($actualValue) && is_numeric($expectedValue) && 
                       floatval($actualValue) <= floatval($expectedValue);
            
            case 'contains':
                return is_string($actualValue) && strpos($actualValue, $expectedValue) !== false;
            
            case 'starts_with':
                return is_string($actualValue) && strpos($actualValue, $expectedValue) === 0;
            
            case 'ends_with':
                return is_string($actualValue) && 
                       substr($actualValue, -strlen($expectedValue)) === $expectedValue;
            
            case 'in_array':
                $expectedArray = is_string($expectedValue) ? explode(',', $expectedValue) : $expectedValue;
                return in_array($actualValue, array_map('trim', $expectedArray));
            
            case 'not_in_array':
                $expectedArray = is_string($expectedValue) ? explode(',', $expectedValue) : $expectedValue;
                return !in_array($actualValue, array_map('trim', $expectedArray));
            
            case 'is_empty':
                return empty($actualValue);
            
            case 'is_not_empty':
                return !empty($actualValue);
            
            default:
                throw new InvalidArgumentException("Unknown operator: {$operator}");
        }
    }

    /**
     * Execute actions defined in the rule
     */
    private function executeActions()
    {
        if (!isset($this->rule['actions']) || !is_array($this->rule['actions'])) {
            return;
        }

        foreach ($this->rule['actions'] as $action) {
            $this->executeAction($action);
        }
    }

    /**
     * Execute a single action
     * 
     * @param array $action - Action definition
     */
    private function executeAction($action)
    {
        $type = $action['type'];
        $field = $action['field'];
        $value = $action['value'];

        switch ($type) {
            case 'set_field':
                $this->setValueInPath($this->processedData, $field, $value);
                $this->log("Action executed: Set {$field} = {$value}");
                break;
            
            case 'increment_field':
                $currentValue = $this->getValueFromPath($this->processedData, $field);
                $newValue = (is_numeric($currentValue) ? floatval($currentValue) : 0) + floatval($value);
                $this->setValueInPath($this->processedData, $field, $newValue);
                $this->log("Action executed: Incremented {$field} by {$value} (new value: {$newValue})");
                break;
            
            case 'append_to_array':
                $currentArray = $this->getValueFromPath($this->processedData, $field);
                if (!is_array($currentArray)) {
                    $currentArray = [];
                }
                $currentArray[] = $value;
                $this->setValueInPath($this->processedData, $field, $currentArray);
                $this->log("Action executed: Appended '{$value}' to array {$field}");
                break;
            
            case 'send_email':
                // Log email action (in real implementation, integrate with mail service)
                $this->log("Action executed: Send email to {$field} with content: {$value}");
                break;
            
            case 'log_event':
                // Log custom event
                $this->log("Event logged: {$field} - {$value}");
                break;
            
            case 'trigger_webhook':
                // Log webhook trigger (in real implementation, make HTTP request)
                $this->log("Action executed: Trigger webhook {$field} with data: {$value}");
                break;
            
            case 'update_status':
                $this->setValueInPath($this->processedData, $field, $value);
                $this->log("Action executed: Updated status {$field} = {$value}");
                break;
            
            case 'calculate':
                // Simple calculator for field values
                $expression = str_replace(['{{field}}', '{{value}}'], 
                                        [$this->getValueFromPath($this->data, $field), $value], 
                                        $field);
                $result = eval("return {$expression};");
                $this->setValueInPath($this->processedData, $field, $result);
                $this->log("Action executed: Calculated {$field} = {$result}");
                break;
            
            default:
                $this->log("Warning: Unknown action type '{$type}' - skipped");
                break;
        }
    }

    /**
     * Get value from nested object using dot notation
     * 
     * @param array $data - Data array
     * @param string $path - Dot notation path (e.g., 'user.profile.age')
     * @return mixed - Value at path or null if not found
     */
    private function getValueFromPath($data, $path)
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Set value in nested object using dot notation
     * 
     * @param array &$data - Data array (passed by reference)
     * @param string $path - Dot notation path
     * @param mixed $value - Value to set
     */
    private function setValueInPath(&$data, $path, $value)
    {
        $keys = explode('.', $path);
        $current = &$data;

        for ($i = 0; $i < count($keys) - 1; $i++) {
            $key = $keys[$i];
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current[end($keys)] = $value;
    }

    /**
     * Validate rule structure
     */
    private function validateRule()
    {
        if (!is_array($this->rule)) {
            throw new InvalidArgumentException('Rule must be an array');
        }

        if (!isset($this->rule['rule_code'])) {
            throw new InvalidArgumentException('Rule must have a name');
        }

        if (!isset($this->rule['conditions'])) {
            throw new InvalidArgumentException('Rule must have conditions');
        }
    }

    /**
     * Add log entry
     * 
     * @param string $message - Log message
     */
    private function log($message)
    {
        $this->logs[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message
        ];
    }

    /**
     * Get processing result
     * 
     * @return array - Result with processed data and logs
     */
    private function getResult()
    {
        return [
            'original_data' => $this->data,
            'processed_data' => $this->processedData,
            'rule_name' => $this->rule['rule_code'],
            'execution_logs' => $this->logs,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>