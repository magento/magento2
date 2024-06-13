<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Model;

/**
 * Report Flag Model
 *
 * @api
 * @since 100.0.2
 */
class Flag extends \Magento\Framework\Flag
{
    public const REPORT_ORDER_FLAG_CODE = 'report_order_aggregated';

    public const REPORT_TAX_FLAG_CODE = 'report_tax_aggregated';

    public const REPORT_SHIPPING_FLAG_CODE = 'report_shipping_aggregated';

    public const REPORT_INVOICE_FLAG_CODE = 'report_invoiced_aggregated';

    public const REPORT_REFUNDED_FLAG_CODE = 'report_refunded_aggregated';

    public const REPORT_COUPONS_FLAG_CODE = 'report_coupons_aggregated';

    public const REPORT_BESTSELLERS_FLAG_CODE = 'report_bestsellers_aggregated';

    public const REPORT_PRODUCT_VIEWED_FLAG_CODE = 'report_product_viewed_aggregated';

    /**
     * Setter for flag code
     *
     * @codeCoverageIgnore
     * @param string $code
     * @return $this
     */
    public function setReportFlagCode($code)
    {
        $this->_flagCode = $code;
        return $this;
    }
}
