<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Source\Locale;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ListsInterface;

/**
 * Locale currency source.
 *
 * @api
 * @since 100.0.2
 */
class Currency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var ListsInterface
     */
    protected $_localeLists;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $installedCurrencies;

    /**
     * @param ListsInterface $localeLists
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ListsInterface $localeLists,
        ScopeConfigInterface $config = null
    ) {
        $this->_localeLists = $localeLists;
        $this->config = $config ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->_localeLists->getOptionCurrencies();
        }

        $selected = array_flip($this->getInstalledCurrencies());

        $options = array_filter(
            $this->_options,
            function ($option) use ($selected) {
                return isset($selected[$option['value']]);
            }
        );

        return $options;
    }

    /**
     * Retrieve Installed Currencies.
     *
     * @return array
     */
    private function getInstalledCurrencies()
    {
        if (!$this->installedCurrencies) {
            $this->installedCurrencies = explode(
                ',',
                $this->config->getValue(
                    'system/currency/installed',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        return $this->installedCurrencies;
    }
}
