<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Elasticsearch5 Product Field Mapper Adapter
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @deprecated
     * @var Config
     */
    protected $eavConfig;

    /**
     * @deprecated
     * @var FieldType
     */
    protected $fieldType;

    /**
     * @deprecated
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @deprecated
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @deprecated
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @var FieldProviderInterface
     */
    private $fieldProvider;

    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     * @param CustomerSession $customerSession
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     * @param ResolverInterface $fieldNameResolver
     * @param AttributeProvider $attributeAdapterProvider
     * @param FieldProviderInterface $fieldProvider
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType,
        CustomerSession $customerSession,
        StoreManager $storeManager,
        Registry $coreRegistry,
        ResolverInterface $fieldNameResolver,
        AttributeProvider $attributeAdapterProvider,
        FieldProviderInterface $fieldProvider
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldProvider = $fieldProvider;
    }

    /**
     * Get field name.
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $attributeAdapter = $this->attributeAdapterProvider->getByAttributeCode($attributeCode);
        return $this->fieldNameResolver->getFieldName($attributeAdapter, $context);
    }

    /**
     * Get all attributes types.
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = [])
    {
        return $this->fieldProvider->getFields($context);
    }
}
