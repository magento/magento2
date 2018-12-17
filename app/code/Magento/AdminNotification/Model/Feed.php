<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model;

use Exception;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use SimpleXMLElement;
use Zend_Http_Client;

/**
 * AdminNotification Feed model
 *
 * @package Magento\AdminNotification\Model
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Feed extends AbstractModel
{
    const XML_USE_HTTPS_PATH = 'system/adminnotification/use_https';

    const XML_FEED_URL_PATH = 'system/adminnotification/feed_url';

    const XML_FREQUENCY_PATH = 'system/adminnotification/frequency';

    const XML_LAST_UPDATE_PATH = 'system/adminnotification/last_update';

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl; //phpcs:ignore

    /**
     * @var ConfigInterface
     */
    protected $_backendConfig; //phpcs:ignore

    /**
     * @var InboxFactory
     */
    protected $_inboxFactory; //phpcs:ignore

    /**
     * @var CurlFactory
     *
     */
    protected $curlFactory;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    protected $_deploymentConfig; //phpcs:ignore

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param CurlFactory $curlFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $backendConfig,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_backendConfig    = $backendConfig;
        $this->_inboxFactory     = $inboxFactory;
        $this->curlFactory       = $curlFactory;
        $this->_deploymentConfig = $deploymentConfig;
        $this->productMetadata   = $productMetadata;
        $this->urlBuilder        = $urlBuilder;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl(): string
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkUpdate()
    {
        if ($this->getFrequency() + $this->getLastUpdate() > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        $installDate = strtotime($this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));

        if ($feedXml
            && !empty($feedXml->channel)
            && !empty($feedXml->channel->item)
        ) {
            foreach ($feedXml->channel->item as $item) {
                if (!empty($item->pubDate)
                    && !empty($item->severity)
                    && !empty($item->title)
                    && !empty($item->description)
                    && !empty($item->link)
                ) {
                    $itemPublicationDate = strtotime((string)$item->pubDate);
                    if ($itemPublicationDate && $installDate <= $itemPublicationDate) {
                        $feedData[] = [
                            'severity' => (int)$item->severity,
                            'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                            'title' => $this->escapeString($item->title),
                            'description' => $this->escapeString($item->description),
                            'url' => $this->escapeString($item->link),
                        ];
                    }
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
    public function getFrequency(): int
    {
        return $this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate(): int
    {
        return (int)$this->_cacheManager->load('admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save((string)time(), 'admin_notifications_lastcheck');
        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement|null
     */
    public function getFeedData(): ?SimpleXMLElement
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
        $curl->write(Zend_Http_Client::GET, $this->getFeedUrl(), '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim((string)$data[1]);
        $curl->close();

        try {
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            return null;
        }

        return $xml;
    }

    /**
     * Retrieve feed as XML element
     *
     * @return SimpleXMLElement
     */
    public function getFeedXml(): SimpleXMLElement
    {
        try {
            $data = $this->getFeedData();
            $xml = new SimpleXMLElement((string)$data);
        } catch (Exception $e) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
        }

        return $xml;
    }

    /**
     * Converts incoming data to string format and escapes special characters.
     *
     * @param SimpleXMLElement $data
     * @return string
     */
    private function escapeString(SimpleXMLElement $data): string
    {
        return htmlspecialchars((string)$data);
    }
}
