<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\Review;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\ReviewApi\Api\Data\ReviewStatusInterface;

/**
 * Review status
 */
class Status extends AbstractExtensibleModel implements ReviewStatusInterface
{
    /**
     * Status constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null
    ) {
        $extensionFactory = $extensionFactory
            ?: ObjectManager::getInstance()->get(ExtensionAttributesFactory::class);
        $customAttributeFactory = $customAttributeFactory
            ?: ObjectManager::getInstance()->get(AttributeValueFactory::class);

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
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Review\Status::class);
    }

    /**
     * @inheritdoc
     */
    public function getStatusId(): ?int
    {
        return $this->_getData(self::STATUS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStatusId(?int $statusId): ReviewStatusInterface
    {
        $this->setData(self::STATUS_ID, $statusId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatusCode(): string
    {
        return $this->_getData(self::STATUS_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setStatusCode(string $statusCode): ReviewStatusInterface
    {
        $this->setData(self::STATUS_CODE, $statusCode);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(ReviewStatusInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface $extensionAttributes
    ): ReviewStatusInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
