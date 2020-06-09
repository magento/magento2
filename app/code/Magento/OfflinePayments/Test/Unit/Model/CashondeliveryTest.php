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
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CashondeliveryTest extends TestCase
{
    /**
     * @var Cashondelivery
     */
    private $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $paymentDataMock = $this->createMock(Data::class);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->object = $helper->getObject(
            Cashondelivery::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    public function testGetInfoBlockType()
    {
        $this->assertEquals(Instructions::class, $this->object->getInfoBlockType());
    }
}
