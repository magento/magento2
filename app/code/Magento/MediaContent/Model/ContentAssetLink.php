<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;

/**
 * Relation of the media asset to the media content
 */
class ContentAssetLink extends AbstractExtensibleModel implements ContentAssetLinkInterface
{
    private const ASSET_ID = 'asset_id';
    private const ENTITY_TYPE = 'entity_type';
    private const ENTITY_ID = 'entity_id';
    private const FIELD = 'field';

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
       $this->contentIdentityFactory = $contentIdentityFactory;
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
    public function getAssetId(): int
    {
        return (int) $this->getData(self::ASSET_ID);
    }

    /**
     * @inheritdoc
     */
    public function getContentId(): ContentIdentityInterface
    {
        return $this->contentIdentityFactory->create(['data' => [
            self::ENTITY_TYPE => $this->getData(self::ENTITY_TYPE),
            self::ENTITY_ID => $this->getData(self::ENTITY_TYPE),
            self::FIELD => $this->getData(self::FIELD)
        ]]);
    }

    /**
     * @inheritdoc
     */
    public function getField(): string
    {
        return (string) $this->getData(self::FIELD);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ContentAssetLinkExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ContentAssetLinkExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
