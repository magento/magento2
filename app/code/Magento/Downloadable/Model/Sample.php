<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\SampleInterface;

/**
 * Downloadable sample model
 *
 * @method int getProductId()
 *
 * @api
 */
class Sample extends \Magento\Framework\Model\AbstractExtensibleModel implements ComponentInterface, SampleInterface
{
    const XML_PATH_SAMPLES_TITLE = 'catalog/downloadable/samples_title';

    /**#@+
     * Constants for field names
     */
    const KEY_TITLE = 'title';
    const KEY_SORT_ORDER = 'sort_order';
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
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Downloadable\Model\ResourceModel\Sample::class);
        parent::_construct();
    }

    /**
     * After save process
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->getResource()->saveItemTitle($this);
        return parent::afterSave();
    }

    /**
     * Retrieve sample URL
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->getSampleUrl()) {
            return $this->getSampleUrl();
        } else {
            return $this->getSampleFile();
        }
    }

    /**
     * Retrieve base tmp path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return 'downloadable/tmp/samples';
    }

    /**
     * Retrieve sample files path
     *
     * @return string
     */
    public function getBasePath()
    {
        return 'downloadable/files/samples';
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
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
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
     * {@inheritdoc}
     * @codeCoverageIgnore
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

    /**
     * Set sample title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set sort order index for sample
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
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
     * Set file path or null when type is 'url'
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
    public function setSampleFileContent(\Magento\Downloadable\Api\Data\File\ContentInterface $sampleFileContent = null)
    {
        return $this->setData(self::KEY_SAMPLE_FILE_CONTENT, $sampleFileContent);
    }

    /**
     * Set sample URL
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
     * @return \Magento\Downloadable\Api\Data\SampleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Downloadable\Api\Data\SampleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
