<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var Config */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'cc_available_types' => $this->config->getCcTypes(),
            'cc_months' => $this->config->getMonths(),
            'cc_years' => $this->config->getYears(),
            'cc_has_verification' => true,
            'cc_has_ss_card_type' => false,
            'cc_ss_start_years' => $this->getSsStartYears(),
        ];
    }

    /**
     * Solo/switch card start years
     *
     * @return array
     */
    protected function getSsStartYears()
    {
        $years = [];
        $first = date("Y");

        for ($index = 5; $index >= 0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = [0 => __('Year')] + $years;
        return $years;
    }
}
