<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Controller\Adminhtml\System;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check Additionally Show Order Total Without Tax Label
     */
    public function testAdditionallyShowOrderTotalWithoutTax()
    {
        $this->dispatch('backend/admin/system_config/edit/section/tax/');
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('id="tax_cart_display_grandtotal"', $body);
        $this->assertStringContainsString('id="tax_sales_display_grandtotal"', $body);
        $this->assertStringContainsString('Additionally Show Order Total Without Tax', $body);
    }
}
