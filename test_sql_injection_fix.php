#!/usr/bin/env php
<?php
/**
 * Standalone test script for SQL Injection Validator
 * 
 * This script tests the validator without requiring PHPUnit framework
 */

// Include the validator class
require_once __DIR__ . '/lib/internal/Magento/Framework/Data/Collection/SqlInjectionValidator.php';

// Mock classes needed for the validator
if (!class_exists('Magento\Framework\Exception\LocalizedException')) {
    // Create a custom exception that accepts Phrase objects
    class Magento_Framework_Exception_LocalizedException extends Exception {
        public function __construct($message, $code = 0, $previous = null) {
            // Convert Phrase objects to string
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

use Magento\Framework\Data\Collection\SqlInjectionValidator;

echo "=" . str_repeat("=", 79) . "\n";
echo "SQL INJECTION VALIDATOR TEST SUITE\n";
echo "=" . str_repeat("=", 79) . "\n\n";

$validator = new SqlInjectionValidator();

// Test counters
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

/**
 * Run a test case
 */
function runTest($testName, $sql, $shouldPass, &$validator, &$totalTests, &$passedTests, &$failedTests) {
    $totalTests++;
    
    try {
        $result = $validator->validate($sql, true);
        
        if ($shouldPass) {
            echo "✓ PASS: $testName\n";
            echo "  SQL: $sql\n\n";
            $passedTests++;
        } else {
            echo "✗ FAIL: $testName (Expected exception but none thrown)\n";
            echo "  SQL: $sql\n\n";
            $failedTests++;
        }
    } catch (Exception $e) {
        if (!$shouldPass) {
            echo "✓ PASS: $testName (Correctly rejected)\n";
            echo "  SQL: $sql\n";
            echo "  Message: " . $e->getMessage() . "\n\n";
            $passedTests++;
        } else {
            echo "✗ FAIL: $testName (Unexpected exception)\n";
            echo "  SQL: $sql\n";
            echo "  Message: " . $e->getMessage() . "\n\n";
            $failedTests++;
        }
    }
}

echo "SAFE SQL PATTERNS (Should Pass)\n";
echo "-" . str_repeat("-", 79) . "\n\n";

// Safe patterns that should pass
$safePatterns = [
    ['name' => 'Quoted field with placeholder', 'sql' => '`customer_id` = ?'],
    ['name' => 'Table.column with placeholder', 'sql' => 'main_table.entity_id = ?'],
    ['name' => 'IN clause with placeholders', 'sql' => 'status IN (?, ?, ?)'],
    ['name' => 'LIKE clause with placeholder', 'sql' => '`email` LIKE ?'],
    ['name' => 'IS NULL check', 'sql' => '`deleted_at` IS NULL'],
    ['name' => 'IS NOT NULL check', 'sql' => 'created_at IS NOT NULL'],
    ['name' => 'BETWEEN with placeholders', 'sql' => '`price` BETWEEN ? AND ?'],
    ['name' => 'Quoted identifier with string', 'sql' => "`name` = 'test'"],
];

foreach ($safePatterns as $pattern) {
    runTest($pattern['name'], $pattern['sql'], true, $validator, $totalTests, $passedTests, $failedTests);
}

echo "\nDANGEROUS SQL PATTERNS (Should Fail)\n";
echo "-" . str_repeat("-", 79) . "\n\n";

// Dangerous patterns that should be blocked
$dangerousPatterns = [
    ['name' => 'UNION SELECT attack', 'sql' => "' UNION SELECT * FROM admin_user WHERE '1'='1"],
    ['name' => 'SQL line comment', 'sql' => "admin' --"],
    ['name' => 'Stacked DELETE query', 'sql' => "value'; DELETE FROM users; --"],
    ['name' => 'Subquery injection', 'sql' => "(SELECT password FROM admin_user WHERE user_id=1)"],
    ['name' => 'Boolean OR injection', 'sql' => "' OR 1=1 --"],
    ['name' => 'SLEEP function (time-based)', 'sql' => "' OR SLEEP(5) --"],
    ['name' => 'DROP TABLE attempt', 'sql' => "'; DROP TABLE users; --"],
    ['name' => 'Information schema access', 'sql' => "' UNION SELECT table_name FROM INFORMATION_SCHEMA.TABLES --"],
    ['name' => 'LOAD_FILE function', 'sql' => "' UNION SELECT LOAD_FILE('/etc/passwd') --"],
    ['name' => 'EXEC command', 'sql' => "'; EXEC sp_executesql N'DROP TABLE users'; --"],
];

foreach ($dangerousPatterns as $pattern) {
    runTest($pattern['name'], $pattern['sql'], false, $validator, $totalTests, $passedTests, $failedTests);
}

// Print summary
echo "\n" . "=" . str_repeat("=", 79) . "\n";
echo "TEST SUMMARY\n";
echo "=" . str_repeat("=", 79) . "\n";
echo "Total Tests:  $totalTests\n";
echo "Passed:       $passedTests (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";
echo "Failed:       $failedTests (" . round(($failedTests/$totalTests)*100, 1) . "%)\n";
echo "=" . str_repeat("=", 79) . "\n\n";

if ($failedTests === 0) {
    echo "✓ ALL TESTS PASSED - SQL INJECTION PROTECTION IS WORKING!\n\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED - REVIEW FAILURES ABOVE\n\n";
    exit(1);
}
