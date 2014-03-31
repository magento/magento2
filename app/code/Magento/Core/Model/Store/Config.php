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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Store;

class Config implements \Magento\Core\Model\Store\ConfigInterface
{
    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Core\Model\Resource\Store\Collection
     */
    protected $_storeCollection;

    /**
     * @var \Magento\Core\Model\Resource\Store\CollectionFactory
     */
    protected $_factory;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\Core\Model\Resource\Store\CollectionFactory $factory
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\ConfigInterface $config,
        \Magento\Core\Model\Resource\Store\CollectionFactory $factory
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_factory = $factory;
    }

    /**
     * Retrieve store config value
     *
     * @param string $path
     * @param null|string|bool|int|\Magento\Core\Model\Store $store
     * @return string|null
     */
    public function getConfig($path, $store = null)
    {
        return $this->_storeManager->getStore($store)->getConfig($path);
    }

    /**
     * Retrieve store config flag
     *
     * @param string $path
     * @param null|string|bool|int|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function getConfigFlag($path, $store = null)
    {
        $flag = strtolower($this->getConfig($path, $store));
        return !empty($flag) && 'false' !== $flag;
    }

    /**
     * Retrieve store Ids for $path with checking
     *
     * If empty $allowValues then retrieve all stores values
     *
     * return array($storeId => $pathValue)
     *
     * @param string $path
     * @param array $allowedValues
     * @param string $keyAttribute
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getStoresConfigByPath($path, $allowedValues = array(), $keyAttribute = 'id')
    {
        if (is_null($this->_storeCollection)) {
            $this->_storeCollection = $this->_factory->create();
            $this->_storeCollection->setLoadDefault(true);
        }
        $storeValues = array();
        /** @var $store \Magento\Core\Model\Store */
        foreach ($this->_storeCollection as $store) {
            switch ($keyAttribute) {
                case 'id':
                    $key = $store->getId();
                    break;
                case 'code':
                    $key = $store->getCode();
                    break;
                case 'name':
                    $key = $store->getName();
                    break;
                default:
                    throw new \InvalidArgumentException("'{$keyAttribute}' cannot be used as a key.");
                    break;
            }

            $value = $this->_config->getValue($path, 'store', $store->getCode());
            if (empty($allowedValues)) {
                $storeValues[$key] = $value;
            } elseif (in_array($value, $allowedValues)) {
                $storeValues[$key] = $value;
            }
        }
        return $storeValues;
    }
}
