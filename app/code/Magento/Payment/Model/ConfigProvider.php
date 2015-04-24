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
            'ccAvailableTypes' => $this->config->getCcTypes(),
            'ccMonths' => $this->config->getMonths(),
            'ccYears' => $this->config->getYears(),
            'ccHasVerification' => true,
            'ccHasSsCardType' => false,
            'ccSsStartYears' => $this->getSsStartYears(),
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
        return $years;
    }
}
