<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Controller\Index;

use Magento\TestFramework\TestCase\AbstractController;
use Zend\Http\Headers;

/**
 * Test component rendering on storefront.
 *
 * @magentoAppArea frontend
 */
class RenderTest extends AbstractController
{
    /**
     * Test content type being chosen based on context.
     *
     * @param string $headers
     * @param string $expectedContentType
     * @dataProvider contentTypeDataProvider
     */
    public function testContentType(string $headers, string $expectedContentType)
    {
        $this->getRequest()->setParam('namespace', 'widget_recently_viewed');
        $this->getRequest()->setHeaders(Headers::fromString('Accept:' . $headers . ''));
        $this->dispatch('mui/index/render');
        $this->assertNotEmpty($contentType = $this->getResponse()->getHeader('Content-Type'));
        $this->assertEquals($expectedContentType, $contentType->getFieldValue());
    }

    /**
     * @return array
     */
    public function contentTypeDataProvider(): array
    {
        return [
            ['application/json', 'application/json'],
            ['text/html', 'text/html'],
            ['', 'text/html'],
        ];
    }
}
