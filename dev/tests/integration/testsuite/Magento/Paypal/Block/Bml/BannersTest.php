<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Bml;

use Magento\TestFramework\Helper\Bootstrap;

class BannersTest extends \PHPUnit_Framework_TestCase
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
        /** @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject $paypalConfig */
        $paypalConfig = $this->getMock(\Magento\Paypal\Model\Config::class, [], [], '', false);
        $paypalConfig->expects($this->any())->method('getBmlPublisherId')->will($this->returnValue($publisherId));
        $paypalConfig->expects($this->any())->method('getBmlDisplay')->will($this->returnValue($display));
        $paypalConfig->expects($this->any())->method('getBmlPosition')->will($this->returnValue($configPosition));

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
            $this->assertContains('data-pp-pubid="' . $block->getPublisherId() . '"', $html);
            $this->assertContains('data-pp-placementtype="' . $block->getSize() . '"', $html);
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
