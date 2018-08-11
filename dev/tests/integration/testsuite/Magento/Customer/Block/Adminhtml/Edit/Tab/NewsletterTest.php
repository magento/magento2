<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class NewsletterTest
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
    public function setUp()
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
    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testRenderingNewsletterBlock()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/customer/index/edit');
        $body = $this->getResponse()->getBody();

        $this->assertContains('\u003Cspan\u003ENewsletter Information\u003C\/span\u003E', $body);
        $this->assertContains('\u003Cinput id=\"_newslettersubscription\"', $body);
        $this->assertNotContains('checked="checked"', $body);
        $this->assertContains('\u003Cspan\u003ESubscribed to Newsletter\u003C\/span\u003E', $body);
        $this->assertContains('\u003ENo Newsletter Found\u003C', $body);
    }
}
