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
namespace Magento\Customer\Model\Address;

/**
 * Customer address config
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Framework\Config\Data
{
    const DEFAULT_ADDRESS_RENDERER = 'Magento\Customer\Block\Address\Renderer\DefaultRenderer';

    const XML_PATH_ADDRESS_TEMPLATE = 'customer/address_templates/';

    const DEFAULT_ADDRESS_FORMAT = 'oneline';

    /**
     * Customer Address Templates per store
     *
     * @var array
     */
    protected $_types = array();

    /**
     * Current store instance
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Default types per store
     * Using for invalid code
     *
     * @var array
     */
    protected $_defaultTypes = array();

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Customer\Model\Address\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Customer\Model\Address\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $cacheId = 'address_format'
    ) {
        parent::__construct($reader, $cache, $cacheId);
        $this->_storeManager = $storeManager;
        $this->_addressHelper = $addressHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Set store
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $this->_storeManager->getStore($store);
        return $this;
    }

    /**
     * Retrieve store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = $this->_storeManager->getStore();
        }
        return $this->_store;
    }

    /**
     * Retrieve address formats
     *
     * @return array
     */
    public function getFormats()
    {
        $store = $this->getStore();
        $storeId = $store->getId();
        if (!isset($this->_types[$storeId])) {
            $this->_types[$storeId] = array();
            foreach ($this->get() as $typeCode => $typeConfig) {
                $path = sprintf('%s%s', self::XML_PATH_ADDRESS_TEMPLATE, $typeCode);
                $type = new \Magento\Framework\Object();
                if (isset(
                    $typeConfig['escapeHtml']
                ) && ($typeConfig['escapeHtml'] == 'true' || $typeConfig['escapeHtml'] == '1')
                ) {
                    $escapeHtml = true;
                } else {
                    $escapeHtml = false;
                }

                $type->setCode(
                    $typeCode
                )->setTitle(
                    (string)$typeConfig['title']
                )->setDefaultFormat(
                    $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
                )->setEscapeHtml(
                    $escapeHtml
                );

                $renderer = isset($typeConfig['renderer']) ? (string)$typeConfig['renderer'] : null;
                if (!$renderer) {
                    $renderer = self::DEFAULT_ADDRESS_RENDERER;
                }

                $type->setRenderer($this->_addressHelper->getRenderer($renderer)->setType($type));

                $this->_types[$storeId][] = $type;
            }
        }

        return $this->_types[$storeId];
    }

    /**
     * Retrieve default address format
     *
     * @return \Magento\Framework\Object
     */
    protected function _getDefaultFormat()
    {
        $store = $this->getStore();
        $storeId = $store->getId();
        if (!isset($this->_defaultTypes[$storeId])) {
            $this->_defaultTypes[$storeId] = new \Magento\Framework\Object();
            $this->_defaultTypes[$storeId]->setCode(
                'default'
            )->setDefaultFormat(
                '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}' .
                '{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}, ' .
                '{{var street}}, {{var city}}, {{var region}} {{var postcode}}, {{var country}}'
            );

            $this->_defaultTypes[$storeId]->setRenderer(
                $this->_addressHelper->getRenderer(
                    self::DEFAULT_ADDRESS_RENDERER
                )->setType(
                    $this->_defaultTypes[$storeId]
                )
            );
        }
        return $this->_defaultTypes[$storeId];
    }

    /**
     * Retrieve address format by code
     *
     * @param string $typeCode
     * @return \Magento\Framework\Object
     */
    public function getFormatByCode($typeCode)
    {
        foreach ($this->getFormats() as $type) {
            if ($type->getCode() == $typeCode) {
                return $type;
            }
        }
        return $this->_getDefaultFormat();
    }
}
