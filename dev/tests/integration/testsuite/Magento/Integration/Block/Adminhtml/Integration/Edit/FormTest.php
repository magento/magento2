<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Edit;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Test for \Magento\Integration\Block\Adminhtml\Integration\Edit\Form
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit\Form
     */
    private $block;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $this->objectManager->create(\Magento\Framework\View\LayoutInterface::class);
        $this->block = $layout->createBlock(\Magento\Integration\Block\Adminhtml\Integration\Edit\Form::class);
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testToHtmlNoExistingIntegrationData()
    {
        $this->assertContains(
            '<form id="edit_form" action="" method="post">',
            $this->block->toHtml()
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testToHtmlWithIntegrationData()
    {
        /** @var \Magento\Framework\Registry $coreRegistry */
        $coreRegistry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $coreRegistry->unregister(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $id = 'idValue';
        $integrationData = [
            \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info::DATA_ID => $id,
        ];
        $coreRegistry->register(Integration::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);

        $html = $this->block->toHtml();

        $this->assertRegExp(
            "/<input id=\"integration_id\" name=\"id\".*value=\"$id\".*type=\"hidden\".*>/",
            $html
        );
    }
}
