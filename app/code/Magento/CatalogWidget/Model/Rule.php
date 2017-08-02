<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Rule for catalog widget
 *
 * @api
 * @since 2.0.0
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var Rule\Condition\CombineFactory
     * @since 2.0.0
     */
    protected $conditionsFactory;

    /**
     * Rule constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Rule\Condition\CombineFactory $conditionsFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     *
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogWidget\Model\Rule\Condition\CombineFactory $conditionsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->conditionsFactory = $conditionsFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConditionsInstance()
    {
        return $this->conditionsFactory->create();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getActionsInstance()
    {
        return null;
    }
}
