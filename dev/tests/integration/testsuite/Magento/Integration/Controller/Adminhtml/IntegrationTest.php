<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Controller\Adminhtml;

use Magento\TestFramework\Bootstrap;

/**
 * \Magento\Integration\Controller\Adminhtml\Integration
 *
 * @magentoDataFixture Magento/Integration/_files/integration_all_permissions.php
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class IntegrationTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var \Magento\Integration\Model\Integration  */
    private $_integration;

    protected function setUp()
    {
        parent::setUp();
        /** @var $integration \Magento\Integration\Model\Integration */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $integration = $objectManager->create(\Magento\Integration\Model\Integration::class);
        $this->_integration = $integration->load('Fixture Integration', 'name');
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/integration/index');
        $response = $this->getResponse()->getBody();

        $this->assertContains('Integrations', $response);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="integrationGrid"]',
                $response
            )
        );
    }

    public function testNewAction()
    {
        $this->dispatch('backend/admin/integration/new');
        $response = $this->getResponse()->getBody();

        $this->assertEquals('new', $this->getRequest()->getActionName());
        $this->assertContains('entry-edit form-inline', $response);
        $this->assertContains('New Integration', $response);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="integration_properties_base_fieldset"]',
                $response
            )
        );
    }

    public function testEditAction()
    {
        $integrationId = $this->_integration->getId();
        $this->getRequest()->setParam('id', $integrationId);
        $this->dispatch('backend/admin/integration/edit');
        $response = $this->getResponse()->getBody();
        $saveLink = 'integration/save/';

        $this->assertContains('entry-edit form-inline', $response);
        $this->assertContains('Edit &quot;' . $this->_integration->getName() . '&quot; Integration', $response);
        $this->assertContains($saveLink, $response);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="integration_properties_base_fieldset"]',
                $response
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="integration_edit_tabs_info_section_content"]',
                $response
            )
        );
    }

    public function testSaveActionUpdateIntegration()
    {
        $integrationId = $this->_integration->getId();
        $integrationName = $this->_integration->getName();
        $this->getRequest()->setParam('id', $integrationId);
        $url = 'http://magento.ll/endpoint_url';
        $this->getRequest()->setPostValue(
            [
                'name' => $integrationName,
                'email' => 'test@magento.com',
                'authentication' => '1',
                'endpoint' => $url,
                'current_password' => Bootstrap::ADMIN_PASSWORD,
            ]
        );
        $this->dispatch('backend/admin/integration/save');
        $this->assertSessionMessages(
            $this->equalTo(["The integration '{$integrationName}' has been saved."]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }

    public function testSaveActionNewIntegration()
    {
        $url = 'http://magento.ll/endpoint_url';
        $integrationName = md5(rand());
        $this->getRequest()->setPostValue(
            [
                'name' => $integrationName,
                'email' => 'test@magento.com',
                'authentication' => '1',
                'endpoint' => $url,
                'current_password' => Bootstrap::ADMIN_PASSWORD,
            ]
        );
        $this->dispatch('backend/admin/integration/save');

        $this->assertSessionMessages(
            $this->equalTo(["The integration '{$integrationName}' has been saved."]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }
}
