<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Adminhtml;

class MainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     */
    public function testConstruct()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
        $accountManagement = $objectManager->create('Magento\Customer\Api\AccountManagementInterface');

        /** @var \Magento\Customer\Helper\View $customerViewHelper */
        $customerViewHelper = $objectManager->create('Magento\Customer\Helper\View');

        $customer = $accountManagement->authenticate('customer@example.com', 'password');
        $request = $objectManager->get('Magento\Framework\App\RequestInterface');
        $request->setParam('customerId', $customer->getId());
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        $block = $layout->createBlock('Magento\Review\Block\Adminhtml\Main');
        $customerName = $customerViewHelper->getCustomerName($customer);
        /** @var \Magento\Framework\Escaper $escaper */
        $escaper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Escaper');
        $this->assertStringMatchesFormat(
            '%A' . __('All Reviews of Customer `%1`', $escaper->escapeHtml($customerName)) . '%A',
            $block->getHeaderHtml()
        );
    }
}
