<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;

class TotalsProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $configData = $this->scopeConfig->getValue('sales/totals_sort');
        $totals =  $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['totals']['children'];
        foreach ($totals as $code => &$total) {
            //convert JS naming style to config naming style
            $code = str_replace('-', '_', $code);
            if (array_key_exists($code, $configData)) {
                $total['sortOrder'] = $configData[$code];
            }
        }
        $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['totals']['children'] = $totals;

        return $jsLayout;
    }
}
