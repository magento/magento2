<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InstantPurchase\Test\Unit\Block;

use Magento\InstantPurchase\Block\Button;
use Magento\InstantPurchase\Model\Config;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Test class for button block
 *
 * Class \Magento\InstantPurchase\Test\Unit\Block\ButtonTest
 */
class ButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Button | \PHPUnit\Framework\MockObject\MockObject
     */
    private $block;

    /**
     * @var Config | \PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $store;

    /**
     * @var Context | \PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * Setup environment for testing
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->store = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManager->expects($this->any())->method('getStore')
            ->willReturn($this->store);

        $this->config = $this->createMock(Config::class);

        $this->context->expects($this->any())->method('getStoreManager')
            ->willReturn($this->storeManager);

        $this->block = $this->getMockBuilder(Button::class)
            ->setConstructorArgs(
                [
                    'context' => $this->context,
                    'instantPurchaseConfig' => $this->config
                ]
            )
            ->setMethods(['getUrl'])
            ->getMock();
    }

    /**
     * Test isEnabled() function
     *
     * @param $currentStoreId
     * @param $isModuleEnabled
     * @param $expected
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($currentStoreId, $isModuleEnabled, $expected)
    {
        $this->store->expects($this->any())->method('getId')
            ->willReturn($currentStoreId);

        $this->config->expects($this->any())->method('isModuleEnabled')
            ->willReturn($isModuleEnabled);

        $this->assertEquals($expected, $this->block->isEnabled());
    }

    /**
     * Data Provider for test isEnabled()
     *
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            'Store With ID = 1 and enable module' => [
                1,
                true,
                true
            ],
            'Store With ID = 1 and disable module' => [
                1,
                false,
                false
            ]
        ];
    }

    /**
     * Test getJsLayout() function
     */
    public function testGetJsLayout()
    {
        $currentStoreId = 1;
        $buttonText = 'Instant Purchased';
        $url = 'https://magento2.com/instantpurchase/button/placeOrder';
        $expected = '{"components":{"instant-purchase":{"config":{"buttonText":"Instant Purchased",' .
            '"purchaseUrl":"https:\/\/magento2.com\/instantpurchase\/button\/placeOrder"}}}}';

        $this->store->expects($this->any())->method('getId')
            ->willReturn($currentStoreId);
        $this->config->expects($this->any())->method('getButtonText')
            ->willReturn($buttonText);
        $this->block->expects($this->any())->method('getUrl')
            ->with('instantpurchase/button/placeOrder', ['_secure' => true])
            ->willReturn($url);

        $this->assertEquals($expected, $this->block->getJsLayout());
    }
}
