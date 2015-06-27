<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

use Magento\Integration\Model\Integration;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Integration/_files/integration_all_permissions.php
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Delete
     */
    protected $deleteButtonBlock;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $objectManager->get('Magento\Framework\App\Request\Http');
        $request->setRouteName('adminhtml')->setControllerName('integration');
        $this->deleteButtonBlock = $objectManager->create(
            'Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Delete'
        );
        $column = $objectManager->create('Magento\Backend\Block\Widget\Grid\Column');
        $this->deleteButtonBlock->setColumn($column);
    }

    public function testRender()
    {
        $integration = $this->getFixtureIntegration();
        $buttonHtml = $this->deleteButtonBlock->render($integration);
        $this->assertContains('title="Remove"', $buttonHtml);
        $this->assertContains(
            'onclick="this.setAttribute(\'data-url\', '
            . '\'http://localhost/index.php/backend/admin/integration/delete/id/'
            . $integration->getId(),
            $buttonHtml
        );
        $this->assertNotContains('disabled', $buttonHtml);
    }

    public function testRenderDisabled()
    {
        $integration = $this->getFixtureIntegration();
        $integration->setSetupType(Integration::TYPE_CONFIG);
        $buttonHtml = $this->deleteButtonBlock->render($integration);
        $this->assertContains('title="Uninstall the extension to remove this integration"', $buttonHtml);
        $this->assertContains(
            'onclick="this.setAttribute(\'data-url\', '
            . '\'http://localhost/index.php/backend/admin/integration/delete/id/'
            . $integration->getId(),
            $buttonHtml
        );
        $this->assertContains('disabled="disabled"', $buttonHtml);
    }

    /**
     * @return Integration
     */
    protected function getFixtureIntegration()
    {
        /** @var $integration Integration */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $integration = $objectManager->create('Magento\Integration\Model\Integration');
        return $integration->load('Fixture Integration', 'name');
    }
}
