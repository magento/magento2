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

class Website implements \Magento\Framework\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @var \Magento\Framework\App\Config\Scope\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\Resource\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     * @param \Magento\Framework\App\Config\Scope\Converter $converter
     * @param \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\ScopePool $scopePool,
        \Magento\Framework\App\Config\Scope\Converter $converter,
        \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_scopePool = $scopePool;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_appState = $appState;
    }

    /**
     * Read configuration by code
     *
     * @param string $code
     * @return array
     */
    public function read($code = null)
    {
        $config = array_replace_recursive(
            $this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT)->getSource(),
            $this->_initialConfig->getData("websites|{$code}")
        );

        if ($this->_appState->isInstalled()) {
            $website = $this->_websiteFactory->create();
            $website->load($code);
            $collection = $this->_collectionFactory->create(
                array('scope' => \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, 'scopeId' => $website->getId())
            );
            $dbWebsiteConfig = array();
            foreach ($collection as $configValue) {
                $dbWebsiteConfig[$configValue->getPath()] = $configValue->getValue();
            }
            $dbWebsiteConfig = $this->_converter->convert($dbWebsiteConfig);

            if (count($dbWebsiteConfig)) {
                $config = array_replace_recursive($config, $dbWebsiteConfig);
            }
        }
        return $config;
    }
}
