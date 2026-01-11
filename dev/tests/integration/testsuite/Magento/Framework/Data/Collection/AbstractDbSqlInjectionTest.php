<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Collection;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Integration test for SQL Injection prevention in AbstractDb collection
 *
 * This test verifies that the SQL injection vulnerability in the 'string' filter
 * type has been fixed and that malicious SQL cannot be injected.
 */
class AbstractDbSqlInjectionTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TestCollection
     */
    private $collection;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        
        // Create a test collection instance
        $this->collection = $this->objectManager->create(TestCollection::class);
    }

    /**
     * Test that properly escaped SQL using quoteInto() works correctly
     *
     * This represents the SAFE pattern that existing Magento code uses.
     */
    public function testProperlyEscapedSqlIsAllowed(): void
    {
        $connection = $this->collection->getConnection();
        
        // This is the SAFE pattern used throughout Magento
        $escapedSql = $connection->quoteInto('customer_id = ?', 123);
        
        // Should not throw exception
        $this->collection->addFilter('test', $escapedSql, 'string');
        
        // Trigger filter rendering
        $this->collection->getSelect();
        
        // If we get here without exception, the test passed
        $this->assertTrue(true);
    }

    /**
     * Test that SQL injection attempt via UNION is blocked
     */
    public function testUnionBasedInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt UNION-based SQL injection
        $maliciousSql = "' UNION SELECT password FROM admin_user WHERE '1'='1";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that SQL injection via stacked queries is blocked
     */
    public function testStackedQueryInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt to stack queries
        $maliciousSql = "test'; DELETE FROM admin_user; --";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that SQL injection via subquery is blocked
     */
    public function testSubqueryInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt subquery injection
        $maliciousSql = "(SELECT password FROM admin_user WHERE user_id=1)";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that comment-based injection is blocked
     */
    public function testCommentBasedInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt comment-based injection
        $maliciousSql = "admin' --";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that boolean-based blind injection is blocked
     */
    public function testBooleanBlindInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt boolean blind injection
        $maliciousSql = "' OR 1=1 --";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that time-based blind injection is blocked
     */
    public function testTimeBasedBlindInjectionIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt time-based blind injection
        $maliciousSql = "' OR SLEEP(5) --";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test that information schema access is blocked
     */
    public function testInformationSchemaAccessIsBlocked(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection/');
        
        // Attempt to access information schema
        $maliciousSql = "' UNION SELECT table_name FROM INFORMATION_SCHEMA.TABLES --";
        $this->collection->addFilter('test', $maliciousSql, 'string');
        
        // Trigger filter rendering
        $this->collection->load();
    }

    /**
     * Test backward compatibility: existing safe code continues to work
     */
    public function testBackwardCompatibilityWithSafePatterns(): void
    {
        $connection = $this->collection->getConnection();
        
        // All these patterns are SAFE and used in existing Magento code
        $safePatterns = [
            $connection->quoteInto('customer_id = ?', 123),
            $connection->quoteInto('email LIKE ?', '%@example.com'),
            $connection->quoteInto('status IN (?, ?)', 'pending') . ', ' . 
                $connection->quote('active'),
            'created_at IS NOT NULL',
        ];
        
        foreach ($safePatterns as $index => $safeSql) {
            $collection = $this->objectManager->create(TestCollection::class);
            $collection->addFilter("test_$index", $safeSql, 'string');
            
            // Should not throw exception
            $collection->getSelect();
        }
        
        // If we get here without exception, backward compatibility is maintained
        $this->assertTrue(true);
    }
}

/**
 * Test collection class for integration testing
 */
class TestCollection extends AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize collection
     */
    protected function _construct()
    {
        // Use customer table for testing (exists in all Magento installations)
        $this->_init(
            \Magento\Framework\DataObject::class,
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
    }
}
