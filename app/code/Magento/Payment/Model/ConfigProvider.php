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
            'payment' => [
                'cc' => [
                    'availableTypes' => $this->getCcAvailableTypes(),
                    'months' => $this->getCcMonths(),
                    'years' => $this->getCcYears(),
                    'hasVerification' => $this->hasVerification(),
                    'hasSsCardType' => $this->hasSsCardType(),
                    'ssStartYears' => $this->getSsStartYears(),
                ]
            ]
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

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    protected function getCcAvailableTypes()
    {
        return $this->config->getCcTypes();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->config->getMonths();
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->config->getYears();
    }

    /**
     * Retrieve has verification configuration
     *
     * @return bool
     */
    protected function hasVerification()
    {
        return true;
    }

    /**
     * Whether switch/solo card type available
     *
     * @return bool
     */
    protected function hasSsCardType()
    {
        return false;
    }
}
