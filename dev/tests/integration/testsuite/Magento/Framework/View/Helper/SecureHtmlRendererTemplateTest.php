<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Helper;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test the secure HTML helper and templates.
 *
 * @magentoAppArea frontend
 */
class SecureHtmlRendererTemplateTest extends AbstractController
{
    /**
     * Test using the helper inside templates.
     *
     * @return void
     */
    public function testTemplateUsage(): void
    {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('securehtml/secure/helper');
        $content = $this->getResponse()->getContent();

        $this->assertStringContainsString(
            '<h1 onclick="alert&#x28;&#x29;">Hello there!</h1>',
            $content
        );
        $this->assertStringContainsString(
            '<script src="http&#x3A;&#x2F;&#x2F;my.magento.com&#x2F;static&#x2F;script.js"/>',
            $content
        );
        $this->assertStringContainsString(
            "<script>\n    let myVar = 1;\n</script>",
            $content
        );
        $this->assertStringContainsString(
            '<div>I am just &lt;a&gt; text</div>',
            $content
        );
    }
}
