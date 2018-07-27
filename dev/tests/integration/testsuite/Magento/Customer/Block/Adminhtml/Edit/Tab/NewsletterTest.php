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
class NewsletterTest extends \PHPUnit_Framework_TestCase
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
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter',
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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testToHtml()
    {
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);
        $html = $this->block->toHtml();

        $this->assertStringStartsWith("<div class=\"entry-edit\">", $html);
        $this->assertContains("<span>Newsletter Information</span>", $html);
        $this->assertContains("type=\"checkbox\"", $html);
        $this->assertNotContains("checked=\"checked\"", $html);
        $this->assertContains("<span>Subscribed to Newsletter</span>", $html);
        $this->assertContains(">No Newsletter Found<", $html);
    }
}
