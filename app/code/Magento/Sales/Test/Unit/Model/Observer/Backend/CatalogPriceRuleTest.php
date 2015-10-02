<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Observer\Backend;

class CatalogPriceRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\CatalogPriceRule
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    protected function setUp()
    {
        $this->eventObserverMock = $this->getMock('Magento\Framework\Event\Observer');
        $this->_quoteMock = $this->getMock('Magento\Quote\Model\Resource\Quote', [], [], '', false);
        $this->_model = new \Magento\Sales\Model\Observer\Backend\CatalogPriceRule($this->_quoteMock);
    }

    public function testDispatch()
    {
        $this->_quoteMock->expects($this->once())->method('markQuotesRecollectOnCatalogRules');
        $this->_model->execute($this->eventObserverMock);
    }
}
