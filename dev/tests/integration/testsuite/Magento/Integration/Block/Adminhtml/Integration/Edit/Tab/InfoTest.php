<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Test for \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
 */
class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
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
        $layout = $this->objectManager->create('Magento\Framework\View\LayoutInterface');
        $this->block = $layout->createBlock('Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info');
        $this->block->addChild('integration_tokens', 'Magento\Integration\Block\Adminhtml\Integration\Tokens');
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testToHtmlNoExistingIntegrationData()
    {
        $this->assertRegExp(
            '/<input id="integration_properties_name" name="name".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_email" name="email".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_endpoint" name="endpoint".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_identity_link_url" name="identity_link_url".*type="text".*>/',
            $this->block->toHtml()
        );
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testToHtmlWithIntegrationData()
    {
        /** @var \Magento\Framework\Registry $coreRegistry */
        $coreRegistry = $this->objectManager->get('\Magento\Framework\Registry');
        $coreRegistry->unregister(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $name = 'nameExample';
        $email = 'admin@example.com';
        $endpoint = 'exampleEndpoint';
        $identityLinkUrl = 'identityLinkUrl';
        $integrationData = [
            Info::DATA_ID => '1',
            Info::DATA_NAME => $name,
            Info::DATA_EMAIL => 'admin@example.com',
            Info::DATA_ENDPOINT => 'exampleEndpoint',
            Info::DATA_IDENTITY_LINK_URL => 'identityLinkUrl',
            Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL,
        ];
        $coreRegistry->register(Integration::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);

        $html = $this->block->toHtml();

        $this->assertRegExp(
            "/<input id=\"integration_properties_name\" name=\"name\".*value=\"$name\".*type=\"text\".*>/",
            $html
        );
        $this->assertRegExp(
            "/<input id=\"integration_properties_email\" name=\"email\".*value=\"$email\".*type=\"text\".*>/",
            $html
        );
        $this->assertRegExp(
            "/<input id=\"integration_properties_endpoint\" name=\"endpoint\".*value=\"$endpoint\".*type=\"text\".*>/",
            $html
        );
        $this->assertRegExp(
            "/<input id=\"integration_properties_identity_link_url\" name=\"identity_link_url\".*"
            . "value=\"$identityLinkUrl\".*type=\"text\".*>/",
            $html
        );
        $this->assertRegExp(
            '/<input id="integration_properties_consumer_key" name="consumer_key".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_consumer_secret" name="consumer_secret".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_token" name="token".*type="text".*>/',
            $this->block->toHtml()
        );
        $this->assertRegExp(
            '/<input id="integration_properties_token_secret" name="token_secret".*type="text".*>/',
            $this->block->toHtml()
        );
    }
}
