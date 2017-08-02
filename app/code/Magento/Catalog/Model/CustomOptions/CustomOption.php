<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor as FileProcessor;

/**
 * Class \Magento\Catalog\Model\CustomOptions\CustomOption
 *
 * @since 2.0.0
 */
class CustomOption extends AbstractExtensibleModel implements CustomOptionInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param FileProcessor $fileProcessor
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        FileProcessor $fileProcessor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fileProcessor = $fileProcessor;
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
     * @inheritDoc
     * @since 2.0.0
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setOptionId($value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getOptionValue()
    {
        $value =  $this->getData(self::OPTION_VALUE);
        if ($value == 'file') {
            /** @var \Magento\Framework\Api\Data\ImageContentInterface $fileInfo */
            $imageContent = $this->getExtensionAttributes()
                ? $this->getExtensionAttributes()->getFileInfo()
                : null;
            if ($imageContent) {
                $value = $this->fileProcessor->processFileContent($imageContent);
            }
        }
        return $value;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setOptionValue($value)
    {
        return $this->setData(self::OPTION_VALUE, $value);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CustomOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
