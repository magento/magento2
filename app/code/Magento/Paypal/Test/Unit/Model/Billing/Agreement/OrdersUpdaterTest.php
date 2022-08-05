<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Billing\Agreement;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Billing\Agreement\OrdersUpdater;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrdersUpdaterTest extends TestCase
{
    /**
     * @var OrdersUpdater
     */
    protected $_model;

    /**
     * @var Registry|MockObject
     */
    protected $_registry;

    /**
     * @var Agreement|MockObject
     */
    protected $_agreementResource;

    protected function setUp(): void
    {
        $this->_registry = $this->createMock(Registry::class);
        $this->_agreementResource = $this->createMock(Agreement::class);

        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            OrdersUpdater::class,
            ['coreRegistry' => $this->_registry, 'agreementResource' => $this->_agreementResource]
        );
    }

    public function testUpdate()
    {
        $agreement = $this->createMock(\Magento\Paypal\Model\Billing\Agreement::class);
        $argument = $this->createMock(Collection::class);

        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_billing_agreement'
        )->willReturn(
            $agreement
        );

        $agreement->expects($this->once())->method('getId')->willReturn('agreement id');
        $this->_agreementResource->expects(
            $this->once()
        )->method(
            'addOrdersFilter'
        )->with(
            $this->identicalTo($argument),
            'agreement id'
        );

        $this->assertSame($argument, $this->_model->update($argument));
    }

    public function testUpdateWhenBillingAgreementIsNotSet()
    {
        $this->expectException('DomainException');
        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_billing_agreement'
        )->willReturn(
            null
        );

        $this->_model->update('any argument');
    }
}
