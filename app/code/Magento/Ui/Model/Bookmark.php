<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Model\ResourceModel\Bookmark\Collection;
use Magento\Ui\Model\ResourceModel\Bookmark as ResourceBookmark;

/**
 * Domain class Bookmark
 * @codeCoverageIgnore
 */
class Bookmark extends AbstractExtensibleModel implements BookmarkInterface
{
    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ResourceBookmark $resource
     * @param Collection $resourceCollection
     * @param DecoderInterface $jsonDecoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ResourceBookmark $resource,
        Collection $resourceCollection,
        DecoderInterface $jsonDecoder,
        array $data = []
    ) {
        $this->jsonDecoder = $jsonDecoder;
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
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::BOOKMARK_ID);
    }

    /**
     * Get user Id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->getData(self::BOOKMARKSPACE);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Is current
     *
     * @return bool
     */
    public function isCurrent()
    {
        return (bool)$this->getData(self::CURRENT);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->getData(self::CONFIG);
        if ($config) {
            return $this->jsonDecoder->decode($config);
        }
        return [];
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::BOOKMARK_ID, $id);
    }

    /**
     * Set user Id
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Set namespace
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        return $this->setData(self::BOOKMARKSPACE, $namespace);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set current
     *
     * @param bool $isCurrent
     * @return $this
     */
    public function setCurrent($isCurrent)
    {
        return $this->setData(self::CURRENT, $isCurrent);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set config
     *
     * @param string $config
     * @return $this
     */
    public function setConfig($config)
    {
        return $this->setData(self::CONFIG, $config);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Ui\Api\Data\BookmarkExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
