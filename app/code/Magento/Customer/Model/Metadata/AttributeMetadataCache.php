<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Config\App\Config\Type\System;

/**
 * Cache for attribute metadata
 */
class AttributeMetadataCache
{
    /**
     * Cache prefix
     */
    const ATTRIBUTE_METADATA_CACHE_PREFIX = 'ATTRIBUTE_METADATA_INSTANCES_CACHE';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var AttributeMetadataInterface[]
     */
    private $attributes;

    /**
     * @var bool
     */
    private $isAttributeCacheEnabled;

    /**
     * @var AttributeMetadataInterfaceFactory
     */
    private $attributeMetadataFactory;

    /**
     * @var OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory
     */
    private $validationRuleFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     * @param StateInterface $state
     * @param AttributeMetadataInterfaceFactory $attributeMetadataFactory
     * @param OptionInterfaceFactory $optionFactory
     * @param ValidationRuleInterfaceFactory $validationRuleFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $state,
        AttributeMetadataInterfaceFactory $attributeMetadataFactory,
        OptionInterfaceFactory $optionFactory,
        ValidationRuleInterfaceFactory $validationRuleFactory,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->state = $state;
        $this->attributeMetadataFactory = $attributeMetadataFactory;
        $this->optionFactory = $optionFactory;
        $this->validationRuleFactory = $validationRuleFactory;
        $this->serializer = $serializer;
    }

    /**
     * Load attributes metadata from cache
     *
     * @param string $entityType
     * @param string $suffix
     * @return AttributeMetadataInterface[]|bool
     */
    public function load($entityType, $suffix = '')
    {
        if (isset($this->attributes[$entityType . $suffix])) {
            return $this->attributes[$entityType . $suffix];
        }
        if ($this->isEnabled()) {
            $cacheKey = self::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix;
            $serializedData = $this->cache->load($cacheKey);
            if ($serializedData) {
                $attributesData = $this->serializer->unserialize($serializedData);
                $attributes = [];
                foreach ($attributesData as $key => $attributeData) {
                    $attributes[$key] = $this->createMetadataAttribute($attributeData);
                }
                $this->attributes[$entityType . $suffix] = $attributes;
                return $attributes;
            }
        }
        return false;
    }

    /**
     * Save attributes metadata to cache
     *
     * @param string $entityType
     * @param AttributeMetadataInterface[] $attributes
     * @param string $suffix
     * @return void
     */
    public function save($entityType, $attributes, $suffix = '')
    {
        $this->attributes[$entityType . $suffix] = $attributes;
        if ($this->isEnabled()) {
            $cacheKey = self::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix;
            $attributesData = [];
            foreach ($attributes as $key => $attribute) {
                $attributesData[$key] = $attribute->__toArray();
            }
            $serializedData = $this->serializer->serialize($attributesData);
            $this->cache->save(
                $serializedData,
                $cacheKey,
                [
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG,
                    System::CACHE_TAG
                ]
            );
        }
    }

    /**
     * Clean attributes metadata cache
     *
     * @return void
     */
    public function clean()
    {
        unset($this->attributes);
        if ($this->isEnabled()) {
            $this->cache->clean(
                [
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG,
                ]
            );
        }
    }

    /**
     * Check if cache enabled
     *
     * @return bool
     */
    private function isEnabled()
    {
        if (null === $this->isAttributeCacheEnabled) {
            $this->isAttributeCacheEnabled = $this->state->isEnabled(Type::TYPE_IDENTIFIER);
        }
        return $this->isAttributeCacheEnabled;
    }

    /**
     * Create and populate with data AttributeMetadataInterface
     *
     * @param array $data
     * @return AttributeMetadataInterface
     */
    private function createMetadataAttribute($data)
    {
        if (isset($data[AttributeMetadataInterface::OPTIONS])) {
            $data[AttributeMetadataInterface::OPTIONS] = $this->createOptions(
                $data[AttributeMetadataInterface::OPTIONS]
            );
        }
        if (isset($data[AttributeMetadataInterface::VALIDATION_RULES])) {
            $validationRules = [];
            foreach ($data[AttributeMetadataInterface::VALIDATION_RULES] as $validationRuleData) {
                $validationRules[] = $this->validationRuleFactory->create(['data' => $validationRuleData]);
            }
            $data[AttributeMetadataInterface::VALIDATION_RULES] = $validationRules;
        }
        return $this->attributeMetadataFactory->create(['data' => $data]);
    }

    /**
     * Create and populate with data OptionInterface
     *
     * @param array $data
     * @return OptionInterface[]
     */
    private function createOptions($data)
    {
        foreach ($data as $key => $optionData) {
            if (isset($optionData[OptionInterface::OPTIONS])) {
                $optionData[OptionInterface::OPTIONS] = $this->createOptions($optionData[OptionInterface::OPTIONS]);
            }
            $data[$key] = $this->optionFactory->create(['data' => $optionData]);
        }
        return $data;
    }
}
