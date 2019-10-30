<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model;

use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * AdminNotification Feed model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Feed extends \Magento\Framework\Model\AbstractModel
{
    const XML_USE_HTTPS_PATH = 'system/adminnotification/use_https';

    const XML_FEED_URL_PATH = 'system/adminnotification/feed_url';

    const XML_FREQUENCY_PATH = 'system/adminnotification/frequency';

    const XML_LAST_UPDATE_PATH = 'system/adminnotification/last_update';

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $_inboxFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     *
     */
    protected $curlFactory;

    /**
     * Deployment configuration
     *
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Escaper $escaper = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_backendConfig = $backendConfig;
        $this->_inboxFactory = $inboxFactory;
        $this->curlFactory = $curlFactory;
        $this->_deploymentConfig = $deploymentConfig;
        $this->productMetadata = $productMetadata;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
    }

    /**
     * Init model
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _construct()
    {
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        if ($this->getFrequency() + $this->getLastUpdate() > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        $installDate = strtotime($this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $itemPublicationDate = strtotime((string)$item->pubDate);
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => $this->escapeString($item->title),
                        'description' => $this->escapeString($item->description),
                        'url' => $this->escapeString($item->link),
                    ];
                }
            }

            if ($feedData) {
                $this->_inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return $this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'admin_notifications_lastcheck');
        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return \SimpleXMLElement
     */
    public function getFeedData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout'   => 2,
                'useragent' => $this->productMetadata->getName()
                    . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')',
                'referer'   => $this->urlBuilder->getUrl('*/*/*')
            ]
        );
        $curl->write(\Zend_Http_Client::GET, $this->getFeedUrl(), '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            $xml = new \SimpleXMLElement($data);
        } catch (\Exception $e) {
            return false;
        }

        return $xml;
    }

    /**
     * Retrieve feed as XML element
     *
     * @return \SimpleXMLElement
     */
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            $xml = new \SimpleXMLElement($data);
        } catch (\Exception $e) {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
        }

        return $xml;
    }

    /**
     * Converts incoming data to string format and escapes special characters.
     *
     * @param \SimpleXMLElement $data
     * @return string
     */
    private function escapeString(\SimpleXMLElement $data)
    {
        return $this->escaper->escapeHtml((string)$data);
    }
}
