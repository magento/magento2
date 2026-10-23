<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swagger\Controller;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class IndexTest extends AbstractController
{
    /**
     * Verify Swagger renders correctly regardless of PageBuilder module state.
     */
    public function testSwaggerWithPageBuilderEnabledOrDisabled(): void
    {
        $this->dispatch('/swagger');

        self::assertSame(200, $this->getResponse()->getStatusCode());
        self::assertFalse(
            $this->_objectManager->get(LayoutInterface::class)->getBlock('google.maps.api')
        );
        self::assertStringContainsString('id="swagger-ui"', $this->getResponse()->getBody());
    }

    #[Config('cms/pagebuilder/enabled', '1')]
    #[Config('cms/pagebuilder/google_maps_api_key', 'test-maps-key')]
    public function testSwaggerWithMapsKey(): void
    {
        $this->assertSwaggerLayout('test-maps-key');
    }

    #[Config('cms/pagebuilder/enabled', '1')]
    #[Config('cms/pagebuilder/google_maps_api_key', '')]
    public function testSwaggerWithoutMapsKey(): void
    {
        $this->assertSwaggerLayout('');
    }

    private function assertSwaggerLayout(string $key): void
    {
        $config = $this->_objectManager->get(ScopeConfigInterface::class);
        self::assertSame(
            $key,
            (string)$config->getValue('cms/pagebuilder/google_maps_api_key')
        );
        $this->dispatch('/swagger');
        self::assertSame(200, $this->getResponse()->getStatusCode());
        $layout = $this->_objectManager->get(LayoutInterface::class);
        self::assertFalse($layout->getBlock('google.maps.api'));
        self::assertNotFalse($layout->getBlock('swaggerUiContent'));
        self::assertStringContainsString('id="swagger-ui"', $this->getResponse()->getBody());
    }
}
