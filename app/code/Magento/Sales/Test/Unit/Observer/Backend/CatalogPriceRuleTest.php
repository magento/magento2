<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Sales\Observer\Backend\CatalogPriceRule;

class CatalogPriceRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CatalogPriceRule
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_quoteMock;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventObserverMock;

    protected function setUp(): void
    {
        $this->eventObserverMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_quoteMock = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote::class);
        $this->_model = new CatalogPriceRule($this->_quoteMock);
    }

    public function testDispatch()
    {
        $this->_quoteMock->expects($this->once())->method('markQuotesRecollectOnCatalogRules');
        $this->_model->execute($this->eventObserverMock);
    }
}
