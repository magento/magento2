<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for \Magento\Integration\Controller\Adminhtml\Integration\TokensExchange.
 *
 * @magentoAppArea adminhtml
 */
class TokensExchangeTest extends AbstractBackendController
{
    private const URL = 'backend/admin/integration/tokensExchange';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationService = $this->_objectManager->get(IntegrationServiceInterface::class);
    }

    /**
     * Activate integration
     *
     * @magentoDataFixture Magento/Integration/_files/integration_all_data.php
     *
     * @return void
     */
    public function testActivate()
    {
        $integration = $this->integrationService->findByName('Fixture Integration');

        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParams(['id' => $integration->getId()]);
        $this->dispatch(self::URL);

        $this->assertStringContainsString(
            'Please setup or sign in into your 3rd party account to complete setup of this integration.',
            $this->getResponse()->getBody()
        );
    }
}
