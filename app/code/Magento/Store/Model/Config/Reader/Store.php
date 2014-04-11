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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\Model\Config\Reader;

class Store implements \Magento\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @var \Magento\Store\Model\Config\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\Resource\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\Config\Initial $initialConfig
     * @param \Magento\App\Config\ScopePool $scopePool
     * @param \Magento\Store\Model\Config\Converter $converter
     * @param \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\App\State $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\Config\Initial $initialConfig,
        \Magento\App\Config\ScopePool $scopePool,
        \Magento\Store\Model\Config\Converter $converter,
        \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_scopePool = $scopePool;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeFactory = $storeFactory;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
    }

    /**
     * Read configuration by code
     *
     * @param string $code
     * @return array
     */
    public function read($code = null)
    {
        if ($this->_appState->isInstalled()) {
            if (empty($code)) {
                $store = $this->_storeManager->getStore();
            } else {
                $store = $this->_storeFactory->create();
                $store->load($code);
            }

            $websiteConfig = $this->_scopePool->getScope(
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $store->getWebsite()->getCode()
            )->getSource();
            $config = array_replace_recursive($websiteConfig, $this->_initialConfig->getData("stores|{$code}"));

            $collection = $this->_collectionFactory->create(
                array('scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORES, 'scopeId' => $store->getId())
            );
            $dbStoreConfig = array();
            foreach ($collection as $item) {
                $dbStoreConfig[$item->getPath()] = $item->getValue();
            }
            $config = $this->_converter->convert($dbStoreConfig, $config);
        } else {
            $websiteConfig = $this->_scopePool->getScope(
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                \Magento\App\ScopeInterface::SCOPE_DEFAULT
            )->getSource();
            $config = $this->_converter->convert($websiteConfig, $this->_initialConfig->getData("stores|{$code}"));
        }
        return $config;
    }
}
