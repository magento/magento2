<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\ResourceModel\Link as Resource;

/**
 * Downloadable link model
 *
 * @api
 * @method int getProductId()
 * @method Link setProductId(int $value)
 *
 * @api
 * @since 100.0.2
 */
class Link extends \Magento\Framework\Model\AbstractExtensibleModel implements ComponentInterface, LinkInterface
{
    const XML_PATH_LINKS_TITLE = 'catalog/downloadable/links_title';

    const XML_PATH_DEFAULT_DOWNLOADS_NUMBER = 'catalog/downloadable/downloads_number';

    const XML_PATH_TARGET_NEW_WINDOW = 'catalog/downloadable/links_target_new_window';

    const XML_PATH_CONFIG_IS_SHAREABLE = 'catalog/downloadable/shareable';

    const LINK_SHAREABLE_YES = 1;

    const LINK_SHAREABLE_NO = 0;

    const LINK_SHAREABLE_CONFIG = 2;

    /**#@+
     * Constants for field names
     */
    const KEY_TITLE = 'title';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_IS_SHAREABLE = 'is_shareable';
    const KEY_PRICE = 'price';
    const KEY_NUMBER_OF_DOWNLOADS = 'number_of_downloads';
    const KEY_LINK_TYPE = 'link_type';
    const KEY_LINK_FILE = 'link_file';
    const KEY_LINK_FILE_CONTENT = 'link_file_content';
    const KEY_LINK_URL = 'link_url';
    const KEY_SAMPLE_TYPE = 'sample_type';
    const KEY_SAMPLE_FILE = 'sample_file';
    const KEY_SAMPLE_FILE_CONTENT = 'sample_file_content';
    const KEY_SAMPLE_URL = 'sample_url';
    /**#@-*/

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Downloadable\Model\ResourceModel\Link::class);
        parent::_construct();
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        $this->getResource()->saveItemTitleAndPrice($this);
        return parent::afterSave();
    }

    /**
     * Retrieve base temporary path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return 'downloadable/tmp/links';
    }

    /**
     * Retrieve Base files path
     *
     * @return string
     */
    public function getBasePath()
    {
        return 'downloadable/files/links';
    }

    /**
     * Retrieve base sample temporary path
     *
     * @return string
     */
    public function getBaseSampleTmpPath()
    {
        return 'downloadable/tmp/link_samples';
    }

    /**
     * Retrieve base sample path
     *
     * @return string
     */
    public function getBaseSamplePath()
    {
        return 'downloadable/files/link_samples';
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getIsShareable()
    {
        return $this->getData(self::KEY_IS_SHAREABLE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getNumberOfDownloads()
    {
        return $this->getData(self::KEY_NUMBER_OF_DOWNLOADS);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkType()
    {
        return $this->getData(self::KEY_LINK_TYPE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkFile()
    {
        return $this->getData(self::KEY_LINK_FILE);
    }

    /**
     * Return file content
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getLinkFileContent()
    {
        return $this->getData(self::KEY_LINK_FILE_CONTENT);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getLinkUrl()
    {
        return $this->getData(self::KEY_LINK_URL);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleType()
    {
        return $this->getData(self::KEY_SAMPLE_TYPE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleFile()
    {
        return $this->getData(self::KEY_SAMPLE_FILE);
    }

    /**
     * Return sample file content when type is 'file'
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null relative file path
     */
    public function getSampleFileContent()
    {
        return $this->getData(self::KEY_SAMPLE_FILE_CONTENT);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getSampleUrl()
    {
        return $this->getData(self::KEY_SAMPLE_URL);
    }

    //@codeCoverageIgnoreStart

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * @param int $isShareable
     * @return $this
     */
    public function setIsShareable($isShareable)
    {
        return $this->setData(self::KEY_IS_SHAREABLE, $isShareable);
    }

    /**
     * Set link price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set number of downloads per user
     * Null for unlimited downloads
     *
     * @param int $numberOfDownloads
     * @return $this
     */
    public function setNumberOfDownloads($numberOfDownloads)
    {
        return $this->setData(self::KEY_NUMBER_OF_DOWNLOADS, $numberOfDownloads);
    }

    /**
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType)
    {
        return $this->setData(self::KEY_LINK_TYPE, $linkType);
    }

    /**
     * Set file path or null when type is 'url'
     *
     * @param string $linkFile
     * @return $this
     */
    public function setLinkFile($linkFile)
    {
        return $this->setData(self::KEY_LINK_FILE, $linkFile);
    }

    /**
     * Set file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent
     * @return $this
     */
    public function setLinkFileContent(\Magento\Downloadable\Api\Data\File\ContentInterface $linkFileContent = null)
    {
        return $this->setData(self::KEY_LINK_FILE_CONTENT, $linkFileContent);
    }

    /**
     * Set URL
     *
     * @param string $linkUrl
     * @return $this
     */
    public function setLinkUrl($linkUrl)
    {
        return $this->setData(self::KEY_LINK_URL, $linkUrl);
    }

    /**
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        return $this->setData(self::KEY_SAMPLE_TYPE, $sampleType);
    }

    /**
     * Set file path
     *
     * @param string $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile)
    {
        return $this->setData(self::KEY_SAMPLE_FILE, $sampleFile);
    }

    /**
     * Set sample file content
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent
     * @return $this
     */
    public function setSampleFileContent(
        \Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent = null
    ) {
        return $this->setData(self::KEY_SAMPLE_FILE_CONTENT, $sampleFileContent);
    }

    /**
     * Set URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        return $this->setData(self::KEY_SAMPLE_URL, $sampleUrl);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Downloadable\Api\Data\LinkExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Downloadable\Api\Data\LinkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
