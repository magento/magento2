<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCoupons\ExportCouponsController;

/**
 * Test export coupon xml
 *
 * Verify export xml
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/SalesRule/_files/cart_rule_with_coupon_list.php
 */
class ExportCouponsXmlTest extends ExportCouponsController
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales_rule/promo_quote/exportCouponsXml';

    /**
     * Test export xml
     *
     * @return void
     */
    public function testExportCsv(): void
    {
        $this->prepareRequest();
        $this->dispatch($this->uri);
        $this->assertStringNotContainsString('404 Error', $this->getResponse()->getBody());
    }
}
