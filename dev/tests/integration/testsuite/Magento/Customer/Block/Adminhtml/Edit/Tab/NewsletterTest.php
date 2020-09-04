<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Customer account form block functionality
 *
 * @magentoAppArea adminhtml
 */
class NewsletterTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var Newsletter
     */
    private $block;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Execute per test initialization.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter::class,
            '',
            ['registry' => $this->coreRegistry]
        )->setTemplate(
            'tab/newsletter.phtml'
        );
    }

    /**
     * Execute post test cleanup
     */
    protected function tearDown(): void
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testRenderingNewsletterBlock()
    {
        $websiteId = 1;
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('\u003Cspan\u003ENewsletter Information\u003C\/span\u003E', $body);
        $this->assertStringContainsString(
            '\u003Cinput id=\"_newslettersubscription_status_' . $websiteId . '\"',
            $body
        );
        $this->assertStringNotContainsString('checked="checked"', $body);
        $this->assertStringContainsString('\u003Cspan\u003ESubscribed to Newsletter\u003C\/span\u003E', $body);
        $this->assertStringContainsString('\u003ENo Newsletter Found\u003C', $body);
    }
}
