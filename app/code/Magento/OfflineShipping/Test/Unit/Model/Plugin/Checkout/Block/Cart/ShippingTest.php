<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Plugin\Checkout\Block\Cart;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Plugin\Checkout\Block\Cart\Shipping
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValue',
                'isSetFlag'
            ])
            ->getMock();

        $this->model = $helper->getObject(
            \Magento\OfflineShipping\Model\Plugin\Checkout\Block\Cart\Shipping::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @dataProvider afterGetStateActiveDataProvider
     */
    public function testAfterGetStateActive($scopeConfigMockReturnValue, $result, $assertResult)
    {
        /** @var \Magento\Checkout\Block\Cart\LayoutProcessor $subjectMock */
        $subjectMock = $this->getMockBuilder(\Magento\Checkout\Block\Cart\LayoutProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock->expects($result ? $this->never() : $this->once())
            ->method('getValue')
            ->willReturn($scopeConfigMockReturnValue);

        $this->assertEquals($assertResult, $this->model->afterIsStateActive($subjectMock, $result));
    }

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
