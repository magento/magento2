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

/**
 * Test class for \Magento\RecurringPayment\Block\Catalog\Product\View\Payment
 */
namespace Magento\RecurringPayment\Block\Catalog\Product\View;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDateHtml()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $product->setIsRecurring('1');
        $product->setRecurringPayment(array('start_date_is_editable' => true));
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);
        $block = $objectManager->create('Magento\RecurringPayment\Block\Catalog\Product\View\Payment');
        $block->setLayout($objectManager->create('Magento\Framework\View\Layout'));

        $html = $block->getDateHtml();
        $this->assertNotEmpty($html);
        $dateFormat = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        )->getDateFormat(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
        );
        $timeFormat = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        )->getTimeFormat(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
        );
        $this->assertContains('dateFormat: "' . $dateFormat . '",', $html);
        $this->assertContains('timeFormat: "' . $timeFormat . '",', $html);
    }
}
