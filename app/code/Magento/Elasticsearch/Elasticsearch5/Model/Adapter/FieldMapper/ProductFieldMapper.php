<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use \Magento\Customer\Model\Session as CustomerSession;

/**
 * Class ProductFieldMapper
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
     * @param ResolverInterface|null $fieldNameResolver
     * @param AttributeProvider|null $attributeAdapterProvider
     * @param FieldProviderInterface|null $fieldProvider
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType,
        CustomerSession $customerSession,
        StoreManager $storeManager,
        Registry $coreRegistry,
        ResolverInterface $fieldNameResolver = null,
        AttributeProvider $attributeAdapterProvider = null,
        FieldProviderInterface $fieldProvider = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->fieldNameResolver = $fieldNameResolver ?: ObjectManager::getInstance()
            ->get(ResolverInterface::class);
        $this->attributeAdapterProvider = $attributeAdapterProvider ?: ObjectManager::getInstance()
            ->get(AttributeProvider::class);
        $this->fieldProvider = $fieldProvider ?: ObjectManager::getInstance()
            ->get(FieldProviderInterface::class);
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
