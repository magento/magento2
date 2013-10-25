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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config\Section\Reader;

class Store
{
    /**
     * @var \Magento\Core\Model\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Core\Model\Config\SectionPool
     */
    protected $_sectionPool;

    /**
     * @var \Magento\Core\Model\Config\Section\Store\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Core\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Core\Model\Config\Initial $initialConfig
     * @param \Magento\Core\Model\Config\SectionPool $sectionPool
     * @param \Magento\Core\Model\Config\Section\Store\Converter $converter
     * @param \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Core\Model\Config\Initial $initialConfig,
        \Magento\Core\Model\Config\SectionPool $sectionPool,
        \Magento\Core\Model\Config\Section\Store\Converter $converter,
        \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory $collectionFactory,
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\App\State $appState
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_sectionPool = $sectionPool;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeFactory = $storeFactory;
        $this->_appState = $appState;
    }

    /**
     * Read configuration by code
     *
     * @param string $code
     * @return array
     */
    public function read($code)
    {
        if ($this->_appState->isInstalled()) {
            $store = $this->_storeFactory->create();
            $store->load($code);
            $websiteConfig = $this->_sectionPool->getSection('website', $store->getWebsite()->getCode())->getSource();
            $config = array_replace_recursive($websiteConfig, $this->_initialConfig->getStore($code));

            $collection = $this->_collectionFactory->create(array('scope' => 'stores', 'scopeId' => $store->getId()));
            $dbStoreConfig = array();
            foreach ($collection as $item) {
                $dbStoreConfig[$item->getPath()] = $item->getValue();
            }
            $config = $this->_converter->convert($dbStoreConfig, $config);
        } else {
            $websiteConfig = $this->_sectionPool->getSection('website', 'default')->getSource();
            $config = $this->_converter->convert($websiteConfig, $this->_initialConfig->getStore($code));
        }
        return $config;
    }
} 
