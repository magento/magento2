<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Adminhtml\Edit;

use Magento\Customer\Model\Customer;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     */
    public function testCustomerOnForm()
    {
        /** @var Customer $customer */
        $customer = Bootstrap::getObjectManager()->create(Customer::class)
            ->setWebsiteId(1)
            ->loadByEmail('customer@example.com');
        $block = Bootstrap::getObjectManager()->create(Form::class);
        $block->setNameInLayout('test_block_name');
        /** @var Escaper $escaper */
        $escaper = Bootstrap::getObjectManager()->get(Escaper::class);
        $this->assertStringMatchesFormat(
            '%A' . __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                '%A',
                $escaper->escapeHtml($customer->getFirstname()),
                $escaper->escapeHtml($customer->getLastname()),
                $escaper->escapeHtml($customer->getEmail())
            ) . '%A',
            $block->toHtml()
        );
    }

    /**
     * Verify review form hidden input will contain all review stores.
     *
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     * @return void
     */
    public function testStoresOnForm(): void
    {
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $review = $registry->registry('review_data');
        $block = Bootstrap::getObjectManager()->create(Form::class);
        $block->setNameInLayout('test_block_name');
        foreach ($review->getStores() as $storeId) {
            $regex = sprintf('/input id="select_stores" (.*) value="%d" type="hidden"/', $storeId);
            $this->assertMatchesRegularExpression(
                $regex,
                $block->toHtml()
            );
        }
    }
}
