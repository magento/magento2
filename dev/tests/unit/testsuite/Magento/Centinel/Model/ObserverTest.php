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
namespace Magento\Centinel\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentFormBlockToHtmlBefore()
    {
        $method = $this->getMock(
            'Magento\Paypal\Model\Payflowpro',
            array('getIsCentinelValidationEnabled'),
            array(),
            '',
            false
        );
        $method->expects($this->once())
            ->method('getIsCentinelValidationEnabled')
            ->will($this->returnValue(true));

        $blockLogo = $this->getMock(
            'Magento\Centinel\Block\Logo',
            array('setMethod'),
            array(),
            '',
            false
        );
        $blockLogo->expects($this->once())
            ->method('setMethod')
            ->with($method);

        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            array('createBlock'),
            array(),
            '',
            false
        );
        $layout->expects($this->once())
            ->method('createBlock')
            ->will($this->returnValue($blockLogo));

        $block = $this->getMock(
            'Magento\Payment\Block\Form\Cc',
            array('getMethod', 'getLayout', 'setChild'),
            array(),
            '',
            false
        );
        $block->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $block->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $block->expects($this->once())
            ->method('setChild')
            ->with('payment.method.payflowprocentinel.logo', $blockLogo);

        $event = $this->getMock(
            'Magento\Framework\Event',
            array('getBlock'),
            array(),
            '',
            false
        );
        $event->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($block));

        $observer = $this->getMock(
            'Magento\Framework\Event\Observer',
            array(),
            array(),
            '',
            false
        );
        $observer->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $this->objectManager->getObject('Magento\Centinel\Model\Observer');

        $this->assertEquals($model->paymentFormBlockToHtmlBefore($observer), $model);
    }
}
