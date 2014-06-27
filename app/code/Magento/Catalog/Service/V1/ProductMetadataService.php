<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel;

/**
 * Class ProductMetadataService
 * @package Magento\Catalog\Service\V1
 */
class ProductMetadataService implements ProductMetadataServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Data\Eav\AttributeMetadataBuilder
     */
    private $attributeMetadataBuilder;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
    ) {
        $this->eavConfig = $eavConfig;
        $this->scopeResolver = $scopeResolver;
        $this->attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata()
    {
        $customAttributes = [];
        foreach ($this->getProductAttributesMetadata() as $attributeMetadata) {
            $customAttributes[] = $attributeMetadata;
        }
        return $customAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductAttributesMetadata()
    {
        return $this->getAllAttributeSetMetadata(self::ENTITY_TYPE_PRODUCT, self::ATTRIBUTE_SET_ID_PRODUCT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributeSetMetadata($entityType, $attributeSetId = 0, $scopeCode = null)
    {
        if (null === $scopeCode) {
            $scopeCode = $this->scopeResolver->getScope()->getCode();
        }
        $object = new \Magento\Framework\Object(
            [
                'store_id' => $scopeCode,
                'attribute_set_id' => $attributeSetId,
            ]
        );
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes($entityType, $object);

        $attributesMetadata = [];
        foreach ($attributeCodes as $attributeCode) {
            try {
                $attributesMetadata[] = $this->getAttributeMetadata($entityType, $attributeCode);
            } catch (NoSuchEntityException $e) {
                //If no such entity, skip
            }
        }
        return $attributesMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($entityType, $attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
        if ($attribute) {
            $attributeMetadata = $this->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw (new NoSuchEntityException('entityType', array($entityType)))
                ->singleField('attributeCode', $attributeCode);
        }
    }

    /**
     * @param  AbstractAttribute $attribute
     * @return Data\Eav\AttributeMetadata
     */
    private function createMetadataAttribute($attribute)
    {
        $data = $this->booleanPrefixMapper($attribute->getData());

        // fill options and validate rules
        $data[AttributeMetadata::OPTIONS] = $attribute->usesSource()
            ? $attribute->getSource()->getAllOptions() : array();
        $data[AttributeMetadata::VALIDATION_RULES] = $attribute->getValidateRules();

        // fill scope
        $data[AttributeMetadata::SCOPE] = $attribute->isScopeGlobal()
            ? 'global' : ($attribute->isScopeWebsite() ? 'website' : 'store');

        $data[AttributeMetadata::FRONTEND_LABEL] = [];
        $data[AttributeMetadata::FRONTEND_LABEL][0] = array(
            FrontendLabel::STORE_ID => 0,
            FrontendLabel::LABEL => $attribute->getFrontendLabel()
        );
        if (is_array($attribute->getStoreLabels())) {
            foreach ($attribute->getStoreLabels() as $storeId => $label) {
                $data[AttributeMetadata::FRONTEND_LABEL][$storeId] = array(
                    FrontendLabel::STORE_ID => $storeId,
                    FrontendLabel::LABEL => $label
                );
            }
        }
        return $this->attributeMetadataBuilder->populateWithArray($data)->create();
    }

    /**
     * Remove 'is_' prefixes for Attribute fields to make DTO interface more natural
     *
     * @param array $attributeFields
     * @return array
     */
    private function booleanPrefixMapper(array $attributeFields)
    {
        $prefix = 'is_';
        foreach ($attributeFields as $key => $value) {
            if (strpos($key, $prefix) !== 0) {
                continue;
            }
            $postfix = substr($key, strlen($prefix));
            if (!isset($attributeFields[$postfix])) {
                $attributeFields[$postfix] = $value;
                unset($attributeFields[$key]);
            }
        }
        return $attributeFields;
    }
}
