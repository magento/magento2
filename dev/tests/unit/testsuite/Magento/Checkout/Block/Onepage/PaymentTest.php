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
namespace Magento\Checkout\Block\Onepage;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $hasRecurringItems
     * @dataProvider hasRecurringItemsDataProvider
     */
    public function testHasRecurringItems($hasRecurringItems)
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $quote = $this->getMock('Magento\Sales\Model\Quote', array(
            'hasRecurringItems',
            '__wakeup'
        ), array(), '', false);
        $quote->expects($this->once())->method('hasRecurringItems')->will($this->returnValue($hasRecurringItems));
        $checkoutSession = $this->getMock('Magento\Checkout\Model\Session', array(
            'getQuote',
            'setStepData'
        ), array(), '', false);
        $checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        /** @var \Magento\Checkout\Block\Onepage\Payment $model */
        $model = $helper->getObject('Magento\Checkout\Block\Onepage\Payment', array(
            'resourceSession' => $checkoutSession
        ));
        $this->assertEquals($hasRecurringItems, $model->hasRecurringItems());
    }

    public function hasRecurringItemsDataProvider()
    {
        return array(
            array(false),
            array(true),
        );
    }
}
