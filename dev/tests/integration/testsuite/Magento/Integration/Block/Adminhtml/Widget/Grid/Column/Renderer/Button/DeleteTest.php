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
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Delete
     */
    protected $deleteButtonBlock;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setRouteName('adminhtml')->setControllerName('integration');
        $this->deleteButtonBlock = $objectManager->create(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button\Delete::class
        );
        $column = $objectManager->create(\Magento\Backend\Block\Widget\Grid\Column::class);
        $this->deleteButtonBlock->setColumn($column);
    }

    public function testRender()
    {
        $integration = $this->getFixtureIntegration();
        $buttonHtml = $this->deleteButtonBlock->render($integration);
        self::assertStringContainsString('title="Remove"', $buttonHtml);
        self::assertStringContainsString(
            'this.setAttribute(\'data-url\', '
            . '\'http://localhost/index.php/backend/admin/integration/delete/id/'
            . $integration->getId(),
            $buttonHtml
        );
        $this->assertStringNotContainsString('disabled', $buttonHtml);
    }

    public function testRenderDisabled()
    {
        $integration = $this->getFixtureIntegration();
        $integration->setSetupType(Integration::TYPE_CONFIG);
        $buttonHtml = $this->deleteButtonBlock->render($integration);
        self::assertStringContainsString(
            'title="' .$this->deleteButtonBlock->escapeHtmlAttr('Uninstall the extension to remove this integration')
            .'"',
            $buttonHtml
        );
        self::assertStringContainsString(
            'this.setAttribute(\'data-url\', '
            . '\'http://localhost/index.php/backend/admin/integration/delete/id/'
            . $integration->getId(),
            $buttonHtml
        );
        self::assertStringContainsString('disabled="disabled"', $buttonHtml);
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
