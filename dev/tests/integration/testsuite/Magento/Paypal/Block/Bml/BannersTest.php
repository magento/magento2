<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @dataProvider testToHtmlDataProvider
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testToHtml($publisherId, $display, $position, $configPosition, $isEmptyHtml)
    {
        $paypalConfig = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $paypalConfig->expects($this->any())->method('getBmlPublisherId')->will($this->returnValue($publisherId));
        $paypalConfig->expects($this->any())->method('getBmlDisplay')->will($this->returnValue($display));
        $paypalConfig->expects($this->any())->method('getBmlPosition')->will($this->returnValue($configPosition));

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface');
        $block = $layout->createBlock(
            'Magento\Paypal\Block\Bml\Banners',
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
            [1, 1, 100, 100, false],
            [0, 1, 100, 100, true],
            [1, 0, 100, 100, true],
            [1, 0, 10, 100, true]
        ];
    }
}
