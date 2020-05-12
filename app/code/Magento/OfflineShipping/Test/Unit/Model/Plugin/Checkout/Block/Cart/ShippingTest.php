<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Plugin\Checkout\Block\Cart;

use Magento\Checkout\Block\Cart\LayoutProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Plugin\Checkout\Block\Cart\Shipping;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValue',
                'isSetFlag'
            ])
            ->getMockForAbstractClass();

        $this->model = $helper->getObject(
            Shipping::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @dataProvider afterGetStateActiveDataProvider
     */
    public function testAfterGetStateActive($scopeConfigMockReturnValue, $result, $assertResult)
    {
        /** @var LayoutProcessor $subjectMock */
        $subjectMock = $this->getMockBuilder(LayoutProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock->expects($result ? $this->never() : $this->once())
            ->method('getValue')
            ->willReturn($scopeConfigMockReturnValue);

        $this->assertEquals($assertResult, $this->model->afterIsStateActive($subjectMock, $result));
    }

    /**
     * @return array
     */
    public function afterGetStateActiveDataProvider()
    {
        return [
            [
                true,
                true,
                true
            ],
            [
                true,
                false,
                true
            ],
            [
                false,
                false,
                false
            ]
        ];
    }
}
