#!/usr/bin/env php
<?php
/**
 * Integration test simulating actual collection usage with SQL injection attempts
 */

// Include necessary files
require_once __DIR__ . '/lib/internal/Magento/Framework/Data/Collection/SqlInjectionValidator.php';

// Mock Magento classes
if (!class_exists('Magento\Framework\Exception\LocalizedException')) {
    class Magento_Framework_Exception_LocalizedException extends Exception {
        public function __construct($message, $code = 0, $previous = null) {
            if (is_object($message) && method_exists($message, '__toString')) {
                $message = (string)$message;
            }
            parent::__construct($message, $code, $previous);
        }
    }
    class_alias('Magento_Framework_Exception_LocalizedException', 'Magento\Framework\Exception\LocalizedException');
}
if (!class_exists('Magento\Framework\Phrase')) {
    class Magento_Framework_Phrase {
        private $text;
        public function __construct($text) { $this->text = $text; }
        public function __toString() { return (string)$this->text; }
    }
    class_alias('Magento_Framework_Phrase', 'Magento\Framework\Phrase');
}

// Mock database connection
class MockConnection {
    public function quoteInto($sql, $value) {
        // Simulate proper escaping
        if (is_numeric($value)) {
            return str_replace('?', $value, $sql);
        }
        $escaped = addslashes($value);
        return str_replace('?', "'$escaped'", $sql);
    }
    
    public function quote($value) {
        return "'" . addslashes($value) . "'";
    }
}

echo "=" . str_repeat("=", 79) . "\n";
echo "SQL INJECTION FIX - INTEGRATION TEST\n";
echo "=" . str_repeat("=", 79) . "\n\n";

$connection = new MockConnection();
$validator = new \Magento\Framework\Data\Collection\SqlInjectionValidator();

$testResults = [];

// Simulate SAFE usage pattern (existing Magento code)
echo "TEST 1: SAFE PATTERN - Using quoteInto() before addFilter()\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $userInput = "123"; // Simulated user input
    $safeSql = $connection->quoteInto('customer_id = ?', $userInput);
    echo "User Input: $userInput\n";
    echo "Escaped SQL: $safeSql\n";
    
    $validator->validate($safeSql);
    
    echo "✓ RESULT: Validation passed (SAFE)\n\n";
    $testResults[] = true;
} catch (Exception $e) {
    echo "✗ RESULT: Unexpected exception: " . $e->getMessage() . "\n\n";
    $testResults[] = false;
}

// Simulate ATTACK attempt #1: UNION-based injection
echo "TEST 2: ATTACK ATTEMPT - UNION-based SQL injection\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $maliciousInput = "123' UNION SELECT password FROM admin_user WHERE '1'='1";
    echo "Malicious Input: $maliciousInput\n";
    echo "Attempting to use raw input without escaping...\n";
    
    $validator->validate($maliciousInput);
    
    echo "✗ RESULT: Attack was NOT blocked (VULNERABILITY EXISTS)\n\n";
    $testResults[] = false;
} catch (Exception $e) {
    echo "✓ RESULT: Attack blocked successfully!\n";
    echo "  Message: " . $e->getMessage() . "\n\n";
    $testResults[] = true;
}

// Simulate ATTACK attempt #2: Stacked queries
echo "TEST 3: ATTACK ATTEMPT - Stacked query injection\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $maliciousInput = "123'; DELETE FROM customers; --";
    echo "Malicious Input: $maliciousInput\n";
    echo "Attempting to inject DELETE statement...\n";
    
    $validator->validate($maliciousInput);
    
    echo "✗ RESULT: Attack was NOT blocked (VULNERABILITY EXISTS)\n\n";
    $testResults[] = false;
} catch (Exception $e) {
    echo "✓ RESULT: Attack blocked successfully!\n";
    echo "  Message: " . $e->getMessage() . "\n\n";
    $testResults[] = true;
}

// Simulate ATTACK attempt #3: Information schema access
echo "TEST 4: ATTACK ATTEMPT - Information schema extraction\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $maliciousInput = "1 UNION SELECT table_name,column_name FROM INFORMATION_SCHEMA.COLUMNS";
    echo "Malicious Input: $maliciousInput\n";
    echo "Attempting to extract database schema...\n";
    
    $validator->validate($maliciousInput);
    
    echo "✗ RESULT: Attack was NOT blocked (VULNERABILITY EXISTS)\n\n";
    $testResults[] = false;
} catch (Exception $e) {
    echo "✓ RESULT: Attack blocked successfully!\n";
    echo "  Message: " . $e->getMessage() . "\n\n";
    $testResults[] = true;
}

// Simulate ATTACK attempt #4: Boolean-based blind injection
echo "TEST 5: ATTACK ATTEMPT - Boolean-based blind injection\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $maliciousInput = "1 OR 1=1 --";
    echo "Malicious Input: $maliciousInput\n";
    echo "Attempting boolean blind injection...\n";
    
    $validator->validate($maliciousInput);
    
    echo "✗ RESULT: Attack was NOT blocked (VULNERABILITY EXISTS)\n\n";
    $testResults[] = false;
} catch (Exception $e) {
    echo "✓ RESULT: Attack blocked successfully!\n";
    echo "  Message: " . $e->getMessage() . "\n\n";
    $testResults[] = true;
}

// Simulate SAFE usage pattern with LIKE clause
echo "TEST 6: SAFE PATTERN - LIKE clause with quoteInto()\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $userInput = "%example.com";
    $safeSql = $connection->quoteInto('email LIKE ?', $userInput);
    echo "User Input: $userInput\n";
    echo "Escaped SQL: $safeSql\n";
    
    $validator->validate($safeSql);
    
    echo "✓ RESULT: Validation passed (SAFE)\n\n";
    $testResults[] = true;
} catch (Exception $e) {
    echo "✗ RESULT: Unexpected exception: " . $e->getMessage() . "\n\n";
    $testResults[] = false;
}

// Simulate SAFE usage pattern with IN clause
echo "TEST 7: SAFE PATTERN - IN clause with proper escaping\n";
echo "-" . str_repeat("-", 79) . "\n";

try {
    $safeSql = "status IN ('pending', 'processing', 'complete')";
    echo "Escaped SQL: $safeSql\n";
    
    $validator->validate($safeSql);
    
    echo "✓ RESULT: Validation passed (SAFE)\n\n";
    $testResults[] = true;
} catch (Exception $e) {
    echo "✗ RESULT: Unexpected exception: " . $e->getMessage() . "\n\n";
    $testResults[] = false;
}

// Print final summary
echo "\n" . "=" . str_repeat("=", 79) . "\n";
echo "INTEGRATION TEST SUMMARY\n";
echo "=" . str_repeat("=", 79) . "\n";

$passed = array_sum($testResults);
$total = count($testResults);
$failed = $total - $passed;

echo "Total Tests: $total\n";
echo "Passed: $passed (" . round(($passed/$total)*100, 1) . "%)\n";
echo "Failed: $failed (" . round(($failed/$total)*100, 1) . "%)\n";
echo "=" . str_repeat("=", 79) . "\n\n";

if ($failed === 0) {
    echo "✓ ALL INTEGRATION TESTS PASSED\n";
    echo "✓ SQL INJECTION VULNERABILITY IS FIXED\n";
    echo "✓ BACKWARD COMPATIBILITY IS MAINTAINED\n\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED - SEE DETAILS ABOVE\n\n";
    exit(1);
}
