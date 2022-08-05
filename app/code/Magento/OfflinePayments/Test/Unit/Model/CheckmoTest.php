<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Payment\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckmoTest extends TestCase
{
    /**
     * @var Checkmo
     */
    private $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $paymentDataMock = $this->createMock(Data::class);
        $this->scopeConfigMock = $this->createPartialMock(ScopeConfigInterface::class, ['getValue', 'isSetFlag']);
        $this->object = $objectManagerHelper->getObject(
            Checkmo::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    public function testGetPayableTo()
    {
        $this->object->setStore(1);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/payable_to', 'store', 1)
            ->willReturn('payable');
        $this->assertEquals('payable', $this->object->getPayableTo());
    }

    public function testGetMailingAddress()
    {
        $this->object->setStore(1);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/mailing_address', 'store', 1)
            ->willReturn('blah@blah.com');
        $this->assertEquals('blah@blah.com', $this->object->getMailingAddress());
    }
}
