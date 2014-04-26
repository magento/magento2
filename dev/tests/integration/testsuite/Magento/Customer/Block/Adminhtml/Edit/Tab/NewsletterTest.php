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
            array('registry' => $this->coreRegistry)
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
        $html = $this->block->initForm()->toHtml();

        $this->assertStringStartsWith("<div class=\"entry-edit\">", $html);
        $this->assertContains("<span>Newsletter Information</span>", $html);
        $this->assertContains("type=\"checkbox\"", $html);
        $this->assertNotContains("checked=\"checked\"", $html);
        $this->assertContains("<span>Subscribed to Newsletter</span>", $html);
        $this->assertContains(">No Newsletter Found<", $html);
    }
}
