<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Sales\Observer\Backend\CatalogPriceRule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogPriceRuleTest extends TestCase
{
    /**
     * @var CatalogPriceRule
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_quoteMock;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserverMock;

    protected function setUp(): void
    {
        $this->eventObserverMock = $this->createMock(Observer::class);
        $this->_quoteMock = $this->createMock(Quote::class);
        $this->_model = new CatalogPriceRule($this->_quoteMock);
    }

    public function testDispatch()
    {
        $this->_quoteMock->expects($this->once())->method('markQuotesRecollectOnCatalogRules');
        $this->_model->execute($this->eventObserverMock);
    }
}
