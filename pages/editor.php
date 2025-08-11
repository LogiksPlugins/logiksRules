<?php
if(!defined('ROOT')) exit('No direct script access allowed');


try {
    if(isset($ruleData['fields'])) $ruleData['fields'] = json_decode($ruleData['fields'], true);
} catch(Exception $e) {}
try {
    if(isset($ruleData['conditions'])) $ruleData['conditions'] = json_decode($ruleData['conditions'], true);
} catch(Exception $e) {}
try {
    if(isset($ruleData['actions'])) $ruleData['actions'] = json_decode($ruleData['actions'], true);
} catch(Exception $e) {}
?>
<style>
body .pageComp.withFixedBar .pageCompContainer {
    padding-top: 40px !important;
}
.rule-builder {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
/*            margin: 20px 0;*/
}
.rule-builder-code {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    /*            margin: 20px 0;*/
    height: 100%;
}
.rule-title {
    height: 40px;
}
.condition-group {
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
    background-color: #f8f9fa;
    min-height: 60px;
    position: relative;
}
.nested-condition-group {
    border: 2px solid #5cb85c;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
    background-color: #f0f8f0;
    min-height: 60px;
    position: relative;
}
.condition-group-header {
    position: absolute;
    top: -10px;
    left: 10px;
    background: #fff;
    padding: 2px 8px;
    font-size: 11px;
    color: #666;
    border-radius: 10px;
    border: 1px solid #ddd;
}
.nested-condition-group .condition-group-header {
    background: #5cb85c;
    color: white;
    border: 1px solid #5cb85c;
}
.group-controls {
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}
.group-operator {
    width: 80px;
    display: inline-block;
    margin-right: 10px;
}
.indent-1 { margin-left: 20px; }
.indent-2 { margin-left: 40px; }
.indent-3 { margin-left: 60px; }
.group-actions {
    float: right;
}
.condition-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin: 5px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.action-item {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 4px;
    padding: 10px;
    margin: 5px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.json-output {
    background: #2d3748;
    color: #e2e8f0;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    max-height: 400px;
    overflow-y: auto;
    height: 100%;
    max-height: calc(100% - 50px);
}
.operator-badge {
    background: #6c757d;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}
.remove-btn {
    cursor: pointer;
    color: #dc3545;
}
.remove-btn:hover {
    color: #c82333;
}
.drag-handle {
    cursor: move;
    color: #6c757d;
}
.field-tag {
    display: inline-block;
    background: #428bca;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin: 2px;
    cursor: pointer;
}
.field-tag .remove-field {
    margin-left: 5px;
    cursor: pointer;
    color: #fff;
}
.field-tag .remove-field:hover {
    color: #ffcccc;
}
.rule-name-input {
    border: none;
    background: transparent;
    font-size: 1.25rem;
    font-weight: bold;
    color: #495057;
}
.rule-name-input:focus {
    outline: none;
    background: #fff;
    border: 1px solid #80bdff;
    border-radius: 4px;
    padding: 5px;
}
.form-control .input-sm {
    height: 30px;
}
</style>
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="rule-builder">
                <div class="d-flex justify-content-between align-items-center mb-3 rule-title">
                    <div class='input-group'>
                        <div class="input-group-addon" style="background: transparent;border: 0px;"><i class='fa fa-pencil'></i></div>
                        <input type="text" class="rule-name-input form-control input-md" value="New Rule" id="ruleName">
                    </div>
                    <div style='padding-left: 10px;'>
                        <i class='fa fa-copy copy_rule_code'></i>
                        <citie id='rule_code_value' class="label label-success"></citie>
                    </div>
                </div>
                <br>
                <!-- Input Fields Management Section -->
                <div class="mb-4">
                    <h6 class="text-muted">Required Input Fields/Parameters *</h6>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" id="newFieldInput" placeholder="Enter field name (e.g., user.email, product.price)">
                                <span class="input-group-btn">
                                    <button class="btn btn-success" id="addFieldFromInput" style="height: 39px;">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;" id="fieldsContainer">
                        <!-- Dynamic field tags will be added here -->
                    </div>
                </div>
                <!-- <div class="mb-4">
                    <h6 class="text-muted">Conditions</h6>
                    <div class="condition-group" id="conditionsContainer">
                        <p class="text-muted text-center m-0">No conditions added yet. Click "Add Condition" to start building your rule.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="logicalOperator" class="control-label">Logical Operator:</label>
                        <select class="form-control input-sm" id="logicalOperator" style="width: auto; display: inline-block;">
                            <option value="AND">AND</option>
                            <option value="OR">OR</option>
                        </select>
                    </div>
                </div> -->

                <div class="mb-4">
                    <h6 class="text-muted">Conditions *</h6>
                    <div class="condition-group" id="conditionsContainer" data-group-id="main" data-level="0">
                        <div class="condition-group-header">Main Group</div>
                        <div class="group-controls">
                            <select class="form-control input-sm group-operator" data-group="main">
                                <option value="AND">AND</option>
                                <option value="OR">OR</option>
                            </select>
                            <div class="group-actions">
                                <button class="btn btn-xs btn-success add-condition-btn" data-group="main" title="Add Condition">
                                    <i class="fa fa-plus"></i> Condition
                                </button>
                                <button class="btn btn-xs btn-info add-group-btn" data-group="main" title="Add Nested Group">
                                    <i class="fa fa-sitemap"></i> Group
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="conditions-list" id="mainConditionsList">
                            <p class="text-muted text-center m-0">No conditions added yet. Click "Condition" to start building your rule.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button class="btn btn-xs btn-success add-action-btn pull-right" title="Add Action">
                        <i class="fa fa-plus"></i> Action
                    </button>
                    <h6 class="text-muted">Actions</h6>
                    <div class="condition-group" id="actionsContainer">
                        <p class="text-muted text-center m-0">No actions added yet. Click "Add Action" to define what happens when conditions are met.</p>
                    </div>
                </div>
            </div>
            <br><br>
        </div>

        <div class="col-lg-4">
            <div class="rule-builder-code">
                <h6><i class="fa fa-code"></i> Generated Business Rule</h6>
                <pre class="json-output p-3" id="jsonOutput">{
"name": "New Rule",
"conditions": [],
"actions": []
}</pre>
            </div>
        </div>
    </div>
</div>

<script>
var EXISTING_RULE = <?=json_encode($ruleData)?>;
let conditionCounter = 0;
let actionCounter = 0;
let groupCounter = 0;
var availableFields = ["created_on", "edited_on"];//'user.age', 'user.name', 'order.total', 'product.category', 'date.current'
var availableOperators = {
    "set_field": "Set Field",
    "update_status": "Update Status",
    "log_event": "Log Event",
    "send_email": "Send Email",
    "trigger_webhook": "Trigger Webhook",
    "increment_field": "Increment Field",
    "append_to_array": "Append to Array"
};
$(document).ready(function() {
    // $('#addCondition').click(addCondition);
    // $('#addAction').click(addAction);

    loadJSON();

    // Add field functionality
    $('#addFieldFromInput, #addFieldBtn').click(function() {
        const newField = $('#newFieldInput').val().trim();
        if (newField && availableFields.indexOf(newField) === -1) {
            availableFields.push(newField);
            updateFieldsDisplay();
            updateFieldDropdowns();
            $('#newFieldInput').val('');
            generateJSON();
        } else if (newField && availableFields.indexOf(newField) !== -1) {
            alert('Field already exists!');
        } else {
            alert('Please enter a valid field name!');
        }
    });

    // Remove field functionality
    $(".rule-builder").on('click', '.remove-field', function(e) {
        e.stopPropagation();
        const fieldToRemove = $(this).closest('.field-tag').data('field');
        const index = availableFields.indexOf(fieldToRemove);
        if (index > -1) {
            availableFields.splice(index, 1);
            updateFieldsDisplay();
            updateFieldDropdowns();
            generateJSON();
        }
    });

    // Enter key support for adding fields
    $('#newFieldInput').keypress(function(e) {
        if (e.which === 13) {
            $('#addFieldFromInput').click();
        }
    });

    // Initialize fields display
    updateFieldsDisplay();

    // Remove condition/action
    $(".rule-builder").on('click', '.remove-btn', function() {
        $(this).closest('.condition-item, .action-item').remove();
        
        // Check if containers are empty and add placeholder text
        if ($('#conditionsContainer .condition-item').length === 0) {
            $('#conditionsContainer .conditions-list').html('<p class="text-muted text-center m-0">No conditions added yet. Click "Add Condition" to start building your rule.</p>');
        }
        if ($('#actionsContainer .action-item').length === 0) {
            $('#actionsContainer').html('<p class="text-muted text-center m-0">No actions added yet. Click "Add Action" to define what happens when conditions are met.</p>');
        }
        
        generateJSON();
    });

    // Event listeners for real-time JSON generation
    $(".rule-builder").on('change input', '#ruleName, #logicalOperator, .field-select, .operator-select, .value-input, .action-type-select, .action-field-input, .action-value-input', function() {
        generateJSON();
    });

    $('#generateJson').click(function() {
        generateJSON();
    });

    // Initialize with empty JSON
    generateJSON();

    // Make items sortable (basic drag functionality)
    let draggedElement = null;

    $(".rule-builder").on('mousedown', '.drag-handle', function(e) {
        draggedElement = $(this).closest('.condition-item, .action-item');
        draggedElement.css('opacity', '0.5');
    });

    $(".rule-builder").on('mouseup', function() {
        if (draggedElement) {
            draggedElement.css('opacity', '1');
            draggedElement = null;
        }
    });

    // Add condition - updated for nested groups
    $(".rule-builder").on('click', '.add-condition-btn', function() {
        const groupId = $(this).data('group');
        addConditionToGroup(groupId);
    });

    // Add nested group
    $(".rule-builder").on('click', '.add-group-btn', function() {
        const parentGroupId = $(this).data('group');
        const parentLevel = parseInt($(`[data-group-id="${parentGroupId}"]`).data('level')) || 0;
        addNestedGroup(parentGroupId, parentLevel + 1);
    });

    // Add Action
    $(".rule-builder").on('click', '.add-action-btn', function() {
        addAction();
    });

    $(".rule-builder").on('click', '.remove-group-btn', function() {
        const groupId = $(this).data('group');
        if (confirm('Are you sure you want to remove this group and all its conditions?')) {
            $(`[data-group-id="${groupId}"]`).remove();
            generateJSON();
        }
    });

    // Copy Rule Code
    $(".rule-builder").on('click', '.copy_rule_code', function() {
        copyDivText("rule_code_value");
    });

    $(".onEditor").removeClass("hidden");
});

// Initialize fields display
function updateFieldsDisplay() {
    const container = $('#fieldsContainer');
    container.empty();
    
    if (availableFields.length === 0) {
        container.append('<p class="text-muted">No fields added yet. Add fields to use in your rule conditions.</p>');
    } else {
        availableFields.forEach(function(field) {
            const fieldTag = `
                <span class="field-tag" data-field="${field}">
                    ${field}
                    <span class="remove-field" title="Remove field">
                        <i class="fa fa-times"></i>
                    </span>
                </span>
            `;
            container.append(fieldTag);
        });
    }
}

// Add nested group
function addNestedGroup(parentGroup, level = 1) {
    groupCounter++;
    const groupId = `group_${groupCounter}`;
    const indentClass = getIndentClass(level);
    
    const groupHtml = `
        <div class="nested-condition-group ${indentClass}" data-group-id="${groupId}" data-level="${level}">
            <div class="condition-group-header">Group ${groupCounter}</div>
            <div class="group-controls">
                <select class="form-control input-sm group-operator" data-group="${groupId}">
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>
                </select>
                <div class="group-actions">
                    <button class="btn btn-xs btn-success add-condition-btn" data-group="${groupId}" title="Add Condition">
                        <i class="fa fa-plus"></i> Condition
                    </button>
                    ${level < 3 ? `<button class="btn btn-xs btn-info add-group-btn" data-group="${groupId}" title="Add Nested Group"><i class="fa fa-sitemap"></i> Group</button>` : ''}
                    <button class="btn btn-xs btn-danger remove-group-btn" data-group="${groupId}" title="Remove Group">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="conditions-list" id="${groupId}ConditionsList">
                <p class="text-muted text-center m-0">No conditions in this group yet.</p>
            </div>
        </div>
    `;
    
    const parentContainer = $(`#${parentGroup}ConditionsList`);
    if (parentContainer.find('p.text-muted').length > 0) {
        parentContainer.empty();
    }
    parentContainer.append(groupHtml);
    generateJSON();
}

// Add condition to specific group
function addConditionToGroup(groupId) {
    if (availableFields.length === 0) {
        alert('Please add some input fields first before creating conditions!');
        return;
    }
    
    conditionCounter++;
    const level = parseInt($(`[data-group-id="${groupId}"]`).data('level')) || 0;
    const indentClass = getIndentClass(level);
    
    const conditionHtml = `
        <div class="condition-item ${indentClass}" data-id="${conditionCounter}" data-group="${groupId}">
            <i class="fa fa-bars drag-handle"></i>
            <select class="form-control input-sm field-select" style="width: 150px; display: inline-block;">
                ${availableFields.map(field => `<option value="${field}">${field}</option>`).join('')}
            </select>
            <select class="form-control input-sm operator-select" style="width: 120px; display: inline-block;">
                <option value="equals">equals</option>
                <option value="not_equals">not equals</option>
                <option value="greater_than">greater than</option>
                <option value="less_than">less than</option>
                <option value="contains">contains</option>
                <option value="starts_with">starts with</option>
            </select>
            <input type="text" class="form-control input-sm value-input" placeholder="Value" style="width: 150px; display: inline-block;">
            <i class="fa fa-times remove-btn" title="Remove condition"></i>
        </div>
    `;
    
    const targetContainer = $(`#${groupId}ConditionsList`);
    if (targetContainer.find('p.text-muted').length > 0) {
        targetContainer.empty();
    }
    targetContainer.append(conditionHtml);
    generateJSON();
}

// Parse conditions recursively
function parseConditions(container, level = 0) {
    const result = {
        operator: container.find('.group-operator').first().val() || 'AND',
        conditions: []
    };
    //console.log(container, container.find('.condition-item, .nested-condition-group').length);
    // Find direct children conditions and groups
    container.find(">.conditions-list").children('.condition-item, .nested-condition-group').each(function() {
        if ($(this).hasClass('condition-item')) {
            const field = $(this).find('.field-select').val();
            const operator = $(this).find('.operator-select').val();
            const value = $(this).find('.value-input').val();
            
            if (field && operator && value) {
                result.conditions.push({
                    field: field,
                    operator: operator,
                    value: value
                });
            }
        } else if ($(this).hasClass('nested-condition-group')) {
            const nestedResult = parseConditions($(this), level + 1);
            if (nestedResult.conditions.length > 0) {
                result.conditions.push(nestedResult);
            }
        }
    });
    
    return result;
}

// Add condition
function addCondition() {
    conditionCounter++;

    const colList = availableFields.map(field => `<option value="${field}">${field}</option>`).join('');

    const ruleList = `<option value="equals">equals</option>
                <option value="not_equals">not equals</option>
                <option value="greater_than">greater than</option>
                <option value="less_than">less than</option>
                <option value="contains">contains</option>
                <option value="starts_with">starts with</option>`;

    const conditionHtml = `
        <div class="condition-item" data-id="${conditionCounter}">
            <i class="fa fa-bars drag-handle"></i>
            <select class="form-control input-sm field-select" style="width: 150px; display: inline-block;">${colList}</select>
            <select class="form-control input-sm operator-select" style="width: 120px; display: inline-block;">${ruleList}</select>
            <input type="text" class="form-control input-sm value-input" placeholder="Value" style="width: 150px; display: inline-block;">
            <i class="fa fa-times remove-btn" title="Remove condition"></i>
        </div>
    `;
    
    if ($('#conditionsContainer p').length > 0) {
        $('#conditionsContainer').empty();
    }
    $('#conditionsContainer').append(conditionHtml);
    generateJSON();
}

// Add action
function addAction() {
    actionCounter++;

    const actionList = Object.keys(availableOperators).map(field => `<option value="${field}">${availableOperators[field]}</option>`).join('');

    const actionHtml = `
        <div class="action-item" data-id="${actionCounter}">
            <i class="fas fa-grip-vertical drag-handle"></i>
            <select class="form-control form-select form-select-sm action-type-select" style="width: 150px;">${actionList}</select>
            <input type="text" class="form-control form-control-sm action-field-input" placeholder="Field/Target" style="width: 150px;">
            <input type="text" class="form-control form-control-sm action-value-input" placeholder="Value" style="width: 150px;">
            <i class="fas fa-times remove-btn" title="Remove action"></i>
        </div>
    `;
    
    if ($('#actionsContainer p').length > 0) {
        $('#actionsContainer').empty();
    }
    $('#actionsContainer').append(actionHtml);
    generateJSON();
}

// Load JSON into UI
function loadJSON() {
    try {
        let ruleData = EXISTING_RULE;
        
        // Clear existing editor state
        clearEditor();

        // Load basic rule info
        if (ruleData.title) {
            $('#ruleName').val(ruleData.title);
        }
        $('#rule_code_value').html(ruleData.rule_code);

        // Load available fields
        if (ruleData.fields && Array.isArray(ruleData.fields)) {
            availableFields = [...ruleData.fields];
            updateFieldsDisplay();
        }

        // Load conditions recursively
        if (ruleData.conditions) {
            loadConditionsIntoGroup(ruleData.conditions, 'main', $('#mainConditionsList'));
            // Set main group operator
            $('[data-group="main"]').val(ruleData.conditions.operator || 'AND');
        }

        // Load actions
        if (ruleData.actions && Array.isArray(ruleData.actions)) {
            loadActionsIntoEditor(ruleData.actions);
        }

        // Update field dropdowns and generate JSON
        updateFieldDropdowns();
        generateJSON();

        lgksToast('JSON rule loaded successfully!');
    } catch (error) {
        alert('Error loading JSON: ' + error.message);
        console.error('JSON Load Error:', error);
    }
}

// Generate JSON
function generateJSON() {
    const ruleName = $('#ruleName').val() || 'New Rule';
    //const logicalOperator = $('#logicalOperator').val();
    
    // const conditions = [];
    // $('#conditionsContainer .condition-item').each(function() {
    //     const field = $(this).find('.field-select').val();
    //     const operator = $(this).find('.operator-select').val();
    //     const value = $(this).find('.value-input').val();
        
    //     if (field && operator && value) {
    //         conditions.push({
    //             field: field,
    //             operator: operator,
    //             value: value
    //         });
    //     }
    // });
    const mainContainer = $('#conditionsContainer');
    const conditions = parseConditions(mainContainer);

    const actions = [];
    $('#actionsContainer .action-item').each(function() {
        const type = $(this).find('.action-type-select').val();
        const field = $(this).find('.action-field-input').val();
        const value = $(this).find('.action-value-input').val();
        
        if (type && field && value) {
            actions.push({
                type: type,
                field: field,
                value: value
            });
        }
    });

    const rule = {
        title: ruleName,
        fields: availableFields,
        conditions: conditions,
        actions: actions,
        updatedAt: new Date().toISOString(),
        enabled: true
    };

    $('#jsonOutput').text(JSON.stringify(rule, null, 2));
    return rule;
}

// Clear the entire editor
function clearEditor() {
    // Reset counters
    conditionCounter = 0;
    actionCounter = 0;
    groupCounter = 0;

    // Clear rule name
    //$('#ruleName').val('New Rule');

    // Clear fields (keep defaults for now, but could be made configurable)
    availableFields = [];
    updateFieldsDisplay();

    // Clear conditions - reset to initial state
    $('#conditionsContainer').html(`
        <div class="condition-group-header">Main Group</div>
        <div class="group-controls">
            <select class="form-control input-sm group-operator" data-group="main">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>
            <div class="group-actions">
                <button class="btn btn-xs btn-success add-condition-btn" data-group="main" title="Add Condition">
                    <i class="fa fa-plus"></i> Condition
                </button>
                <button class="btn btn-xs btn-info add-group-btn" data-group="main" title="Add Nested Group">
                    <i class="fa fa-sitemap"></i> Group
                </button>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="conditions-list" id="mainConditionsList">
            <p class="text-muted text-center m-0">No conditions added yet. Click "Condition" to start building your rule.</p>
        </div>
    `);

    // Clear actions
    $('#actionsContainer').html('<p class="text-muted text-center m-0">No actions added yet. Click "Add Action" to define what happens when conditions are met.</p>');

    generateJSON();
}

// Export JSON
function exportJSON() {
    const rule = generateJSON();
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(rule, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", (rule.name || 'rule') + ".json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}

// Update field dropdowns in conditions
function updateFieldDropdowns() {
    const fieldSelects = $('.field-select');
    fieldSelects.each(function() {
        const that = this;
        const currentValue = $(this).val();
        $(this).empty();
        
        if (availableFields.length === 0) {
            $(this).append('<option value="">No fields available</option>');
        } else {
            availableFields.forEach(function(field) {
                const selected = field === currentValue ? 'selected' : '';
                $(that).append(`<option value="${field}" ${selected}>${field}</option>`);
            });
        }
    });
}

// Get indent class based on nesting level
function getIndentClass(level) {
    return level > 0 ? `indent-${Math.min(level, 3)}` : '';
}

// Recursively load conditions into groups
function loadConditionsIntoGroup(conditionsData, groupId, containerElement) {
    if (!conditionsData.conditions || !Array.isArray(conditionsData.conditions)) {
        return;
    }

    // Clear existing placeholder text
    if (containerElement.find('p.text-muted').length > 0) {
        containerElement.empty();
    }

    conditionsData.conditions.forEach(function(condition) {
        if (condition.field) {
            // Simple condition - add to current group
            addConditionToGroup(groupId);
            
            // Get the last added condition and populate it
            const lastCondition = containerElement.find('.condition-item').last();
            lastCondition.find('.field-select').val(condition.field);
            lastCondition.find('.operator-select').val(condition.operator);
            lastCondition.find('.value-input').val(condition.value);

        } else if (condition.operator && condition.conditions) {
            // Nested group - create nested group and load recursively
            const parentLevel = parseInt($(`[data-group-id="${groupId}"]`).data('level')) || 0;
            addNestedGroup(groupId, parentLevel + 1);
            
            // Get the last added group
            const lastGroup = containerElement.find('.nested-condition-group').last();
            const newGroupId = lastGroup.data('group-id');
            const newGroupContainer = lastGroup.find('.conditions-list').first();
            
            // Set the group operator
            lastGroup.find('.group-operator').first().val(condition.operator);
            
            // Recursively load conditions into the new group
            loadConditionsIntoGroup(condition, newGroupId, newGroupContainer);
        }
    });
}

// Load actions into editor
function loadActionsIntoEditor(actionsData) {
    // Clear existing actions
    const actionsContainer = $('#actionsContainer');
    actionsContainer.empty();

    actionsData.forEach(function(action) {
        actionCounter++;
        const actionHtml = `
            <div class="action-item" data-id="${actionCounter}">
                <i class="fa fa-bars drag-handle"></i>
                <select class="form-control input-sm action-type-select" style="width: 150px; display: inline-block;">
                    <option value="set_field">Set Field</option>
                    <option value="send_email">Send Email</option>
                    <option value="log_event">Log Event</option>
                    <option value="trigger_webhook">Trigger Webhook</option>
                    <option value="update_status">Update Status</option>
                    <option value="increment_field">Increment Field</option>
                    <option value="append_to_array">Append to Array</option>
                </select>
                <input type="text" class="form-control input-sm action-field-input" placeholder="Field/Target" style="width: 150px; display: inline-block;" value="${action.field || ''}">
                <input type="text" class="form-control input-sm action-value-input" placeholder="Value" style="width: 150px; display: inline-block;" value="${action.value || ''}">
                <i class="fa fa-times remove-btn" title="Remove action"></i>
            </div>
        `;
        
        actionsContainer.append(actionHtml);
        
        // Set the action type
        const lastAction = actionsContainer.find('.action-item').last();
        lastAction.find('.action-type-select').val(action.type);
    });
}

function copyDivText(divBlock) {
  const text = document.getElementById("divBlock").innerText;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    // Modern method
    navigator.clipboard.writeText(text)
      .then(() => lgksToast("Copied to clipboard!"))
      .catch(err => console.error("Failed to copy: ", err));
  } else {
    // Fallback for older browsers
    const tempTextArea = document.createElement("textarea");
    tempTextArea.value = text;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    try {
      document.execCommand("copy");
      lgksToast("Copied to clipboard!");
    } catch (err) {
      console.error("Fallback copy failed: ", err);
    }
    document.body.removeChild(tempTextArea);
  }
}
</script>