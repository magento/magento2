<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Annotation;

/**
 * Test class for \Magento\TestFramework\Annotation\DbIsolation.
 *
 * @magentoDbIsolation enabled
 */
class DbIsolationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\DbIsolation
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = new \Magento\TestFramework\Annotation\DbIsolation();
    }

    public function testStartTestTransactionRequestClassIsolationEnabled()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testStartTestTransactionRequestMethodIsolationEnabled()
    {
        $this->testStartTestTransactionRequestClassIsolationEnabled();
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testStartTestTransactionRequestMethodIsolationDisabled()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDbIsolation invalid
     */
    public function testStartTestTransactionRequestInvalidAnnotation()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->_object->startTestTransactionRequest($this, new \Magento\TestFramework\Event\Param\Transaction());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testStartTestTransactionRequestAmbiguousAnnotation()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->_object->startTestTransactionRequest($this, new \Magento\TestFramework\Event\Param\Transaction());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testEndTestTransactionRequestMethodIsolationEnabled()
    {
        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new \Magento\TestFramework\Event\Param\Transaction();
        $this->_object->startTransaction($this);
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionStartRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }
}
