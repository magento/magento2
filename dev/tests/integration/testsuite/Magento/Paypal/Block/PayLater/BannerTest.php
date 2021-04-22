<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

class BannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     * @dataProvider getJsLayoutDataProvider
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getConfig()
     */
    public function testGetJsLayout($layoutConfig, $expectedConfig)
    {
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $blockConfig['jsLayout']['components']['payLater']['config']['attributes'] = $layoutConfig;
        $block = $layout->createBlock(
            \Magento\Paypal\Block\PayLater\Banner::class,
            '',
            ['data' => $blockConfig]
        );

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $optionsConfig = $jsConfig['components']['payLater']['config'];
        $this->assertEquals($expectedConfig, array_intersect_key($optionsConfig, $expectedConfig));
    }

    /**
     * @return array
     */
    public function getJsLayoutDataProvider()
    {
        return [
            [
                ['data-pp-placement' => 'test-page'],
                ['attributes' => ['data-pp-placement' => 'test-page', 'data-pp-style-logo-position' => 'right']]
            ],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @covers \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers \Magento\Paypal\Block\PayLater\Banner::getPayPalSdkUrl()
     */
    public function testSdkUrl()
    {
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(
            \Magento\Paypal\Block\PayLater\Banner::class,
            '',
            []
        );

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $this->assertArrayHasKey('sdkUrl', $jsConfig['components']['payLater']['config']);
    }
}
