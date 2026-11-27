<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swagger\Controller;

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
     * Verify Swagger renders correctly when PageBuilder is disabled.
     */
    #[Config('cms/pagebuilder/enabled', '0')]
    public function testSwaggerWithPageBuilderDisabled(): void
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
        $this->assertSwaggerLayout();
    }

    #[Config('cms/pagebuilder/enabled', '1')]
    #[Config('cms/pagebuilder/google_maps_api_key', '')]
    public function testSwaggerWithoutMapsKey(): void
    {
        $this->assertSwaggerLayout();
    }

    private function assertSwaggerLayout(): void
    {
        $this->dispatch('/swagger');
        self::assertSame(200, $this->getResponse()->getStatusCode());
        $layout = $this->_objectManager->get(LayoutInterface::class);
        self::assertFalse($layout->getBlock('google.maps.api'));
        self::assertNotFalse($layout->getBlock('swaggerUiContent'));
        self::assertStringContainsString('id="swagger-ui"', $this->getResponse()->getBody());
    }
}
