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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * \Directory currency abstract backend model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model\Config\Backend\Currency;

abstract class AbstractCurrency extends \Magento\Core\Model\Config\Value
{
    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Constructor
     *
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\ConfigInterface $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
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
                (string)$this->_config->getValue('currency/options/allow', $this->getScope(), $this->getScopeId())
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
        return explode(',', $this->_coreStoreConfig->getConfig('system/currency/installed'));
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
