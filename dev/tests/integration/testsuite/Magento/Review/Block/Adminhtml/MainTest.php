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
        /** @var \Magento\Customer\Service\V1\CustomerAccountService $service */
        $service = $objectManager->create('Magento\Customer\Service\V1\CustomerAccountService');
        $customer = $service->authenticate('customer@example.com', 'password');
        $request = $objectManager->get('Magento\Framework\App\RequestInterface');
        $request->setParam('customerId', $customer->getId());
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        $block = $layout->createBlock('Magento\Review\Block\Adminhtml\Main');
        $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        /** @var \Magento\Framework\Escaper $escaper */
        $escaper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Escaper');
        $this->assertStringMatchesFormat(
            '%A' . __('All Reviews of Customer `%1`', $escaper->escapeHtml($customerName)) . '%A',
            $block->getHeaderHtml()
        );
    }
}
