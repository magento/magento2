<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Layout;

use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class AbstractTotalsProcessor
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $totals
     * @return array
     */
    public function sortTotals($totals)
    {
        $configData = $this->scopeConfig->getValue('sales/totals_sort');
        foreach ($totals as $code => &$total) {
            //convert JS naming style to config naming style
            $code = str_replace('-', '_', $code);
            if (array_key_exists($code, $configData)) {
                $total['sortOrder'] = $configData[$code];
            }
        }

        return $totals;
    }
}
