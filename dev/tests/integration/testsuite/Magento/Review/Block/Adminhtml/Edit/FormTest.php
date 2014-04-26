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

namespace Magento\Review\Block\Adminhtml\Edit;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     */
    public function testCustomerOnForm()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')
            ->setWebsiteId(1)
            ->loadByEmail('customer@example.com');
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Review\Block\Adminhtml\Edit\Form');
        /** @var \Magento\Framework\Escaper $escaper */
        $escaper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Escaper');
        $this->assertStringMatchesFormat(
            '%A' . __('<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                '%A',
                $escaper->escapeHtml($customer->getFirstname()),
                $escaper->escapeHtml($customer->getLastname()),
                $escaper->escapeHtml($customer->getEmail())
            ) . '%A',
            $block->toHtml()
        );
    }
}
