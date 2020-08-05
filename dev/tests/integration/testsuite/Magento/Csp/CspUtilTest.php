<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test CSP util use cases.
 *
 * @magentoAppArea frontend
 */
class CspUtilTest extends AbstractController
{
    /**
     * Test that CSP helper for templates works.
     *
     * @return void
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/inline 0
     */
    public function testPhtmlHelper(): void
    {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('csputil/csp/helper');
        $content = $this->getResponse()->getContent();

        $this->assertStringContainsString(
            '<script src="http&#x3A;&#x2F;&#x2F;my.magento.com&#x2F;static&#x2F;script.js"/>',
            $content
        );
        $this->assertStringContainsString("<script>\n    let myVar = 1;\n</script>", $content);
        $header = $this->getResponse()->getHeader('Content-Security-Policy');
        $this->assertNotEmpty($header);
        $this->assertStringContainsString('http://my.magento.com', $header->getFieldValue());
        $this->assertStringContainsString('\'sha256-H4RRnauTM2X2Xg/z9zkno1crqhsaY3uKKu97uwmnXXE=\'', $header->getFieldValue());
    }
}
