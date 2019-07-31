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
     */
    public function testContentType()
    {
        $this->getRequest()->setParam('namespace', 'widget_recently_viewed');
        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));
        $this->dispatch('mui/index/render');
        $this->assertNotEmpty($contentType = $this->getResponse()->getHeader('Content-Type'));
        $this->assertEquals('application/json', $contentType->getFieldValue());
    }
}
