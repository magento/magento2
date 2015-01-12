<?php
/**
 * Bundle Option Type Source Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Source\Option;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Api\MetadataServiceInterface;

class Type extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Framework\Option\ArrayInterface,
    \Magento\Bundle\Api\Data\OptionTypeInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param array $options
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        array $options,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get Bundle Option Type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = [];
        foreach ($this->options as $value => $label) {
            $types[] = ['label' => $label, 'value' => $value];
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData('code');
    }
}
