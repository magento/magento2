<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * \Directory currency abstract backend model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model\Config\Backend\Currency;

abstract class AbstractCurrency extends \Magento\Framework\App\Config\Value
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve allowed currencies for current scope
     *
     * @return array
     */
    protected function _getAllowedCurrencies()
    {
        if ($this->getData('groups/options/fields/allow/inherit')) {
            return explode(
                ',',
                (string)$this->_config->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
                    $this->getScope(),
                    $this->getScopeId()
                )
            );
        }
        return $this->getData('groups/options/fields/allow/value');
    }

    /**
     * Retrieve Installed Currencies
     *
     * @return string[]
     */
    protected function _getInstalledCurrencies()
    {
        return explode(
            ',',
            $this->_scopeConfig->getValue(
                'system/currency/installed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Retrieve Base Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyBase()
    {
        if (!($value = $this->getData('groups/options/fields/base/value'))) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }

    /**
     * Retrieve Default display Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyDefault()
    {
        if (!($value = $this->getData('groups/options/fields/default/value'))) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }
}
