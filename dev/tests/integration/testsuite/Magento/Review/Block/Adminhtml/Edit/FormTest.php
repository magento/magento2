<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
            ->create(\Magento\Customer\Model\Customer::class)
            ->setWebsiteId(1)
            ->loadByEmail('customer@example.com');
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Review\Block\Adminhtml\Edit\Form::class);
        /** @var \Magento\Framework\Escaper $escaper */
        $escaper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Escaper::class);
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
