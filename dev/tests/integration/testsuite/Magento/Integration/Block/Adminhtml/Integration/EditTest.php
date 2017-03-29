<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Edit
 *
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit
     */
    protected $editBlock;

    protected function setUp()
    {
        $this->editBlock = Bootstrap::getObjectManager()
            ->create(\Magento\Integration\Block\Adminhtml\Integration\Edit::class);
    }

    public function testGetHeaderTextNewIntegration()
    {
        $this->assertEquals('New Integration', $this->editBlock->getHeaderText()->getText());
        $buttonList = Bootstrap::getObjectManager()
            ->get(\Magento\Backend\Block\Widget\Context::class)
            ->getButtonList()
            ->getItems();

        // Assert that there is a 'save' and 'activate' button when creating a new integration
        $haveSaveButton = false;
        foreach ($buttonList as $button) {
            foreach ($button as $key => $value) {
                if ($key === 'save') {
                    $haveSaveButton = true;
                    $this->assertNotNull($value->getDataByKey('options'));
                    $this->assertEquals(
                        'activate',
                        $value->getDataByKey('options')['save_activate']['id'],
                        "'Activate' button is expected when creating a new integration."
                    );
                }
            }
        }
        $this->assertTrue($haveSaveButton, "'Save' button is expected when creating a new integration.");
    }

    public function testGetHeaderTextEditIntegration()
    {
        $integrationId = 1;
        $integrationName = 'Test Name';

        $integrationData = [
            Integration::ID => $integrationId,
            Integration::NAME => $integrationName,
        ];

        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->register(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);

        $headerText = $this->editBlock->getHeaderText();
        $this->assertEquals("Edit Integration '%1'", $headerText->getText());
        $this->assertEquals($integrationName, $headerText->getArguments()[0]);

        // Tear down
        $registry->unregister(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
    }

    public function testGetHeaderTextEditIntegrationConfigType()
    {
        $integrationId = 2;
        $integrationName = 'Test Name 2';

        $integrationData = [
            Integration::ID => $integrationId,
            Integration::NAME => $integrationName,
            Integration::SETUP_TYPE => Integration::TYPE_CONFIG
        ];

        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->register(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);

        /** @var \Magento\Integration\Block\Adminhtml\Integration\Edit $editBlock */
        $editBlock = Bootstrap::getObjectManager()
            ->create(\Magento\Integration\Block\Adminhtml\Integration\Edit::class);

        $headerText = $editBlock->getHeaderText();
        $this->assertEquals("Edit Integration '%1'", $headerText->getText());
        $this->assertEquals($integrationName, $headerText->getArguments()[0]);

        $buttonList = Bootstrap::getObjectManager()
            ->get(\Magento\Backend\Block\Widget\Context::class)
            ->getButtonList()
            ->getItems();

        // Assert that 'save' button is removed for integration of config type
        foreach ($buttonList as $button) {
            $this->assertFalse(array_key_exists('save', $button));
        }

        // Tear down
        $registry->unregister(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
    }

    public function testGetFormActionUrl()
    {
        $baseUrl = Bootstrap::getObjectManager()->get(\Magento\Framework\Url::class)->getBaseUrl();
        $this->assertContains($baseUrl, $this->editBlock->getFormActionUrl());
    }
}
