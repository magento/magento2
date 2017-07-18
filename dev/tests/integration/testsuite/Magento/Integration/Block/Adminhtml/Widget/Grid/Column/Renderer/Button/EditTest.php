<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

use Magento\Integration\Model\Integration;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Integration/_files/integration_all_permissions.php
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Edit
     */
    protected $editButtonBlock;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setRouteName('adminhtml')->setControllerName('integration');
        $this->editButtonBlock = $objectManager->create(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Edit::class
        );
        $column = $objectManager->create(\Magento\Backend\Block\Widget\Grid\Column::class);
        $this->editButtonBlock->setColumn($column);
    }

    public function testRenderEdit()
    {
        $integration = $this->getFixtureIntegration();
        $buttonHtml = $this->editButtonBlock->render($integration);
        $this->assertContains('title="Edit"', $buttonHtml);
        $this->assertContains('class="action edit"', $buttonHtml);
        $this->assertContains(
            'onclick="window.location.href=\'http://localhost/index.php/backend/admin/integration/edit/id/'
            . $integration->getId(),
            $buttonHtml
        );
    }

    public function testRenderView()
    {
        $integration = $this->getFixtureIntegration();
        $integration->setSetupType(Integration::TYPE_CONFIG);
        $buttonHtml = $this->editButtonBlock->render($integration);
        $this->assertContains('title="View"', $buttonHtml);
        $this->assertContains('class="action info"', $buttonHtml);
    }

    /**
     * @return Integration
     */
    protected function getFixtureIntegration()
    {
        /** @var $integration Integration */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $integration = $objectManager->create(\Magento\Integration\Model\Integration::class);
        return $integration->load('Fixture Integration', 'name');
    }
}
