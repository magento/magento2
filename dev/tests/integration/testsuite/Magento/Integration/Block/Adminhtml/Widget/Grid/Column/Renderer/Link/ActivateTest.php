<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;

use Magento\Integration\Model\Integration;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Integration/_files/integration_all_permissions.php
 */
class ActivateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link\Activate
     */
    protected $activateLinkBlock;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->activateLinkBlock = $objectManager->create(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link\Activate::class
        );
        $column = $objectManager->create(\Magento\Backend\Block\Widget\Grid\Column::class);
        $this->activateLinkBlock->setColumn($column);
    }

    public function testRenderActivate()
    {
        $integration = $this->getFixtureIntegration();
        $buttonHtml = $this->activateLinkBlock->render($integration);
        $this->assertStringContainsString('href="javascript:void(0);"', $buttonHtml);
        $this->assertStringContainsString('title="Activate"', $buttonHtml);
        $this->assertStringContainsString('data-row-id="' . $integration->getId() . '"', $buttonHtml);
        $this->assertStringContainsString('data-row-dialog="permissions"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-reauthorize="0"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-token-exchange="0"', $buttonHtml);
        $this->assertStringContainsString('onclick="integration.popup.show(this);', $buttonHtml);
        $this->assertStringContainsString('>Activate</a>', $buttonHtml);
    }

    public function testRenderReauthorize()
    {
        $integration = $this->getFixtureIntegration();
        $integration->setStatus(Integration::STATUS_ACTIVE);
        $buttonHtml = $this->activateLinkBlock->render($integration);
        $this->assertStringContainsString('href="javascript:void(0);"', $buttonHtml);
        $this->assertStringContainsString('title="Reauthorize"', $buttonHtml);
        $this->assertStringContainsString('data-row-id="' . $integration->getId() . '"', $buttonHtml);
        $this->assertStringContainsString('data-row-dialog="permissions"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-reauthorize="1"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-token-exchange="0"', $buttonHtml);
        $this->assertStringContainsString('onclick="integration.popup.show(this);', $buttonHtml);
        $this->assertStringContainsString('>Reauthorize</a>', $buttonHtml);
    }

    /**
     * @param string $endpoint
     * @param string $identityLinkUrl
     * @param int $expectedResult
     * @dataProvider renderTokenExchangeProvider
     */
    public function testRenderTokenExchange($endpoint, $identityLinkUrl, $expectedResult)
    {
        $integration = $this->getFixtureIntegration();
        $integration->setStatus(Integration::STATUS_ACTIVE);
        $integration->setEndpoint($endpoint);
        $integration->setIdentityLinkUrl($identityLinkUrl);
        $buttonHtml = $this->activateLinkBlock->render($integration);
        $this->assertStringContainsString('href="javascript:void(0);"', $buttonHtml);
        $this->assertStringContainsString('title="Reauthorize"', $buttonHtml);
        $this->assertStringContainsString('data-row-id="' . $integration->getId() . '"', $buttonHtml);
        $this->assertStringContainsString('data-row-dialog="permissions"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-reauthorize="1"', $buttonHtml);
        $this->assertStringContainsString('data-row-is-token-exchange="' . $expectedResult . '"', $buttonHtml);
        $this->assertStringContainsString('onclick="integration.popup.show(this);', $buttonHtml);
        $this->assertStringContainsString('>Reauthorize</a>', $buttonHtml);
    }

    public static function renderTokenExchangeProvider()
    {
        return [
            ['http://example.com/endpoint', 'http://example.com/identity', 1],
            ['', null, 0],
            ['http://example.com/endpoint', '', 0],
            [null, 'http://example.com/identity', 0],
        ];
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
