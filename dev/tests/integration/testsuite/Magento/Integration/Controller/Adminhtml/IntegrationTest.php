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
 *
 */
namespace Magento\Integration\Controller\Adminhtml;

/**
 * \Magento\Integration\Controller\Adminhtml\Integration
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class IntegrationTest extends \Magento\Backend\Utility\Controller
{
    /** @var \Magento\Integration\Model\Integration  */
    private $_integration;

    protected function setUp()
    {
        parent::setUp();
        $this->_createDummyIntegration();
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/integration/index');
        $response = $this->getResponse()->getBody();

        $this->assertContains('Integrations', $response);
        $this->assertSelectCount('#integrationGrid', 1, $response);
    }

    public function testNewAction()
    {
        $this->dispatch('backend/admin/integration/new');
        $response = $this->getResponse()->getBody();

        $this->assertEquals('new', $this->getRequest()->getActionName());
        $this->assertContains('entry-edit form-inline', $response);
        $this->assertContains('New Integration', $response);
        $this->assertSelectCount('#integration_properties_base_fieldset', 1, $response);
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
        $this->assertSelectCount('#integration_properties_base_fieldset', 1, $response);
        $this->assertSelectCount('#integration_edit_tabs_info_section_content', 1, $response);
    }

    public function testSaveActionUpdateIntegration()
    {
        $integrationId = $this->_integration->getId();
        $integrationName = $this->_integration->getName();
        $this->getRequest()->setParam('id', $integrationId);
        $url = 'http://magento.ll/endpoint_url';
        $this->getRequest()->setPost(
            array(
                'name' => $integrationName,
                'email' => 'test@magento.com',
                'authentication' => '1',
                'endpoint' => $url
            )
        );
        $this->dispatch('backend/admin/integration/save');
        $this->assertSessionMessages(
            $this->equalTo(array("The integration '{$integrationName}' has been saved.")),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }

    public function testSaveActionNewIntegration()
    {
        $url = 'http://magento.ll/endpoint_url';
        $integrationName = md5(rand());
        $this->getRequest()->setPost(
            array(
                'name' => $integrationName,
                'email' => 'test@magento.com',
                'authentication' => '1',
                'endpoint' => $url
            )
        );
        $this->dispatch('backend/admin/integration/save');

        $this->assertSessionMessages(
            $this->equalTo(array("The integration '{$integrationName}' has been saved.")),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }

    /**
     * Creates a dummy integration for use in dispatched methods under testing
     */
    private function _createDummyIntegration()
    {
        /** @var $factory \Magento\Integration\Model\Integration\Factory */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $factory = $objectManager->create('Magento\Integration\Model\Integration\Factory');
        $this->_integration = $factory->create()->setName(md5(rand()))->save();

        /** Grant permissions to integrations */
        /** @var \Magento\Integration\Service\V1\AuthorizationService $authorizationService */
        $authorizationService = $objectManager->create('Magento\Integration\Service\V1\AuthorizationService');
        $authorizationService->grantAllPermissions($this->_integration->getId());
    }
}
