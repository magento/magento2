<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locale currency source
 */
namespace Magento\Config\Model\Config\Source\Locale;

/**
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
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var Currency
     */
    protected $_installedCurrencies;


    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(
    	\Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    )
    {
        $this->_localeLists = $localeLists;
        $this->_config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->_localeLists->getOptionCurrencies();
        }

        $selected = $this->_getInstalledCurrencies();
        
        $options = array_filter(
            $this->_options,
            function ($option) use ($selected) {
                return in_array($option['value'], $selected);
            }
        );
 
        return $options;
    }
    
    /**
     * Retrieve Installed Currencies
     *
     * @return string[]
     */
    protected function _getInstalledCurrencies()
    {
        if (!$this->_installedCurrencies)
        {
        	$this->_installedCurrencies = explode(
            	',',
            	$this->_config->getValue(
                	'system/currency/installed',
                	\Magento\Store\Model\ScopeInterface::SCOPE_STORE
            	)
        	);
        }
        return $this->_installedCurrencies;
    }

}
