<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Bml;

use Magento\TestFramework\Helper\Bootstrap;

class BannersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param int $publisherId
     * @param int $display
     * @param int $position
     * @param int $configPosition
     * @param bool $isEmptyHtml
     * @param string $methodWppBml
     * @param string $methodWppPeBml
     * @dataProvider testToHtmlDataProvider
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testToHtml(
        $publisherId,
        $display,
        $position,
        $configPosition,
        $isEmptyHtml,
        $methodWppBml,
        $methodWppPeBml
    ) {
        /** @var \Magento\Paypal\Model\Config|\PHPUnit\Framework\MockObject\MockObject $paypalConfig */
        $paypalConfig = $this->createMock(\Magento\Paypal\Model\Config::class);
        $paypalConfig->expects($this->any())->method('getBmlPublisherId')->willReturn($publisherId);
        $paypalConfig->expects($this->any())->method('getBmlDisplay')->willReturn($display);
        $paypalConfig->expects($this->any())->method('getBmlPosition')->willReturn($configPosition);

        $paypalConfig->expects($this->any())
            ->method('isMethodAvailable')
            ->willReturnMap(
                [
                    [
                        $methodWppBml,
                        true,
                    ],
                    [
                        $methodWppPeBml,
                        true,
                    ],
                ]
            );

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class);
        $block = $layout->createBlock(
            \Magento\Paypal\Block\Bml\Banners::class,
            '',
            [
                'paypalConfig' => $paypalConfig,
                'data' => ['position' => $position]
            ]
        );
        $block->setTemplate('bml.phtml');
        $html = $block->toHtml();

        if ($isEmptyHtml) {
            $this->assertEmpty($html);
        } else {
            $this->assertStringContainsString('data-pp-pubid="' . $block->getPublisherId() . '"', $html);
            $this->assertStringContainsString('data-pp-placementtype="' . $block->getSize() . '"', $html);
        }
    }

    /**
     * @return array
     */
    public function testToHtmlDataProvider()
    {
        return [
            [
                'publisherId' => 1,
                'display' => 1,
                'position' => 100,
                'configPosition' => 100,
                'isEmptyHtml' => false,
                'methodWppBml' => 'paypal_express_bml',
                'methodWppPeBml' => 'payflow_express_bml',
            ],
            [
                'publisherId' => 0,
                'display' => 1,
                'position' => 100,
                'configPosition' => 100,
                'isEmptyHtml' => true,
                'methodWppBml' => 'paypal_express_bml',
                'methodWppPeBml' => 'payflow_express_bml',
            ],
            [
                'publisherId' => 1,
                'display' => 0,
                'position' => 100,
                'configPosition' => 100,
                'isEmptyHtml' => true,
                'methodWppBml' => 'paypal_express_bml',
                'methodWppPeBml' => 'payflow_express_bml',
            ],
            [
                'publisherId' => 1,
                'display' => 0,
                'position' => 10,
                'configPosition' => 100,
                'isEmptyHtml' => true,
                'methodWppBml' => 'paypal_express_bml',
                'methodWppPeBml' => 'payflow_express_bml',
            ]
        ];
    }
}
