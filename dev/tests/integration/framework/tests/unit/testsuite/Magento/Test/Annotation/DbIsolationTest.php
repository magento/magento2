<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Annotation;

/**
 * Test class for \Magento\TestFramework\Annotation\DbIsolation.
 *
 * @magentoDbIsolation enabled
 */
class DbIsolationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\DbIsolation
     */
    protected $_object;

    protected function setUp()
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
     * @expectedException \Magento\Framework\Exception
     */
    public function testStartTestTransactionRequestInvalidAnnotation()
    {
        $this->_object->startTestTransactionRequest($this, new \Magento\TestFramework\Event\Param\Transaction());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDbIsolation disabled
     * @expectedException \Magento\Framework\Exception
     */
    public function testStartTestTransactionRequestAmbiguousAnnotation()
    {
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
