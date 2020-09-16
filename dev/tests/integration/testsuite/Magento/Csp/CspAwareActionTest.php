<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test that controllers can modify CSPs for a page.
 *
 * @magentoAppArea frontend
 */
class CspAwareActionTest extends AbstractController
{
    /**
     * Check that a CSP aware action can modify CSPs after ALL other policies had been gathered.
     *
     * @return void
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/policies/storefront/script/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/script/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/script/hosts/example http://controller.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/script/self 0
     * @magentoConfigFixture default_store csp/policies/storefront/script/inline 0
     */
    public function testAwareAction(): void
    {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('csputil/csp/aware');
        $header = $this->getResponse()->getHeader('Content-Security-Policy');
        $this->assertNotEmpty($header);

        $this->assertStringContainsString(
            'script-src https://controller.magento.com'
                .' \'self\' \'sha256-H4RRnauTM2X2Xg/z9zkno1crqhsaY3uKKu97uwmnXXE=\'',
            $header->getFieldValue()
        );
    }
}
