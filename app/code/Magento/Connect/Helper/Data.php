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
namespace Magento\Connect\Helper;

/**
 * Default helper of the module
 */
class Data extends \Magento\Core\Helper\Data
{
    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Convert\Xml
     */
    protected $_xmlConverter;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $readDirectory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Convert\Xml $xmlConverter
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Convert\Xml $xmlConverter,
        $dbCompatibleMode = true
    ) {
        $this->filesystem = $filesystem;
        $this->readDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::VAR_DIR);
        $this->_xmlConverter = $xmlConverter;
        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $appState,
            $priceCurrency,
            $dbCompatibleMode
        );
    }

    /**
     * Retrieve a map to convert a channel from previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapFromV1x()
    {
        return array(
            'connect.magentocommerce.com/community' => 'community',
            'connect.magentocommerce.com/core' => 'community'
        );
    }

    /**
     * Retrieve a map to convert a channel to previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getChannelMapToV1x()
    {
        return array('community' => 'connect.magentocommerce.com/community');
    }

    /**
     * Convert package channel in order for it to be compatible with current version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelFromV1x($channel)
    {
        $channelMap = $this->getChannelMapFromV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Convert package channel in order for it to be compatible with previous version of Magento Connect Manager
     *
     * @param string $channel
     *
     * @return string
     */
    public function convertChannelToV1x($channel)
    {
        $channelMap = $this->getChannelMapToV1x();
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }

    /**
     * Load local package data array
     *
     * @param string $packageName without extension
     * @return array|boolean
     */
    public function loadLocalPackage($packageName)
    {
        $xmlFile = sprintf('connect/%.xml', $packageName);
        $serFile = sprintf('connect/%.ser', $packageName);
        if ($this->readDirectory->isFile($xmlFile) && $this->readDirectory->isReadable($xmlFile)) {
            $xml = simplexml_load_string($this->readDirectory->readFile($xmlFile));
            $data = $this->_xmlConverter->xmlToAssoc($xml);
            if (!empty($data)) {
                return $data;
            }
        }
        if ($this->readDirectory->isFile($serFile) && $this->readDirectory->isReadable($xmlFile)) {
            $data = unserialize($this->readDirectory->readFile($serFile));
            if (!empty($data)) {
                return $data;
            }
        }
        return false;
    }
}
