<?php
// Usage Example
try {
    // Sample rule (from the rule engine designer)
    $sampleRule = [
        'name' => 'User Classification Rule',
        'availableFields' => ['user.age', 'user.status', 'order.total'],
        'conditions' => [
            'operator' => 'AND',
            'conditions' => [
                [
                    'field' => 'user.age',
                    'operator' => 'greater_than',
                    'value' => '18'
                ],
                [
                    'operator' => 'OR',
                    'conditions' => [
                        [
                            'field' => 'user.status',
                            'operator' => 'equals',
                            'value' => 'premium'
                        ],
                        [
                            'field' => 'order.total',
                            'operator' => 'greater_than',
                            'value' => '100'
                        ]
                    ]
                ]
            ]
        ],
        'actions' => [
            [
                'type' => 'set_field',
                'field' => 'user.category',
                'value' => 'qualified_customer'
            ],
            [
                'type' => 'increment_field',
                'field' => 'user.score',
                'value' => '10'
            ]
        ],
        'enabled' => true
    ];

    // Sample data
    $sampleData = [
        'user' => [
            'age' => 25,
            'status' => 'premium',
            'score' => 50,
            'name' => 'John Doe'
        ],
        'order' => [
            'total' => 150.00,
            'items' => 3
        ]
    ];

    // Create rule engine instance
    $ruleEngine = new LogiksRulesEngine();

    // Process the data
    $result = $ruleEngine->process($sampleRule, $sampleData);

    // Display results
    echo "=== RULE PROCESSING RESULT ===\n\n";
    echo "Rule Name: " . $result['rule_name'] . "\n";
    echo "Processing Time: " . $result['timestamp'] . "\n\n";

    echo "Original Data:\n";
    echo json_encode($result['original_data'], JSON_PRETTY_PRINT) . "\n\n";

    echo "Processed Data:\n";
    echo json_encode($result['processed_data'], JSON_PRETTY_PRINT) . "\n\n";

    echo "Execution Logs:\n";
    foreach ($result['execution_logs'] as $log) {
        echo "[{$log['timestamp']}] {$log['message']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Additional helper function for batch processing
function processMultipleRecords($rule, $dataArray) {
    $ruleEngine = new LogiksRulesEngine();
    $results = [];
    
    foreach ($dataArray as $index => $data) {
        try {
            $result = $ruleEngine->process($rule, $data);
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

// Example usage for batch processing
$batchData = [
    [
        'user' => ['age' => 17, 'status' => 'basic', 'score' => 0],
        'order' => ['total' => 50]
    ],
    [
        'user' => ['age' => 30, 'status' => 'premium', 'score' => 75],
        'order' => ['total' => 200]
    ],
    [
        'user' => ['age' => 22, 'status' => 'basic', 'score' => 25],
        'order' => ['total' => 150]
    ]
];

echo "\n\n=== BATCH PROCESSING EXAMPLE ===\n";
$batchResults = processMultipleRecords($sampleRule, $batchData);

foreach ($batchResults as $result) {
    echo "\nRecord {$result['index']}: ";
    if ($result['success']) {
        echo "SUCCESS\n";
        echo "  Conditions Met: " . (count($result['result']['execution_logs']) > 1 ? 'YES' : 'NO') . "\n";
        if (isset($result['result']['processed_data']['user']['category'])) {
            echo "  Category: " . $result['result']['processed_data']['user']['category'] . "\n";
        }
    } else {
        echo "ERROR - " . $result['error'] . "\n";
    }
}