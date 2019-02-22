<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\EavGraphQl\Model\Resolver\DataProvider\AttributeOptions as AttributeOptionsDataProvider;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve attribute options data for custom attribute.
 */
class AttributeOptions implements ResolverInterface
{
    /**
     * @var AttributeOptionsDataProvider
     */
    private $attributeOptionsDataProvider;

    /**
     * @var AttributeOptions
     */
    private $valueFactory;

    /**
     * @param AttributeOptionsDataProvider $attributeOptionsDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        AttributeOptionsDataProvider $attributeOptionsDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->attributeOptionsDataProvider = $attributeOptionsDataProvider;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {

        return $this->valueFactory->create(function () use ($value) {
            $entityType = $this->getEntityType($value);
            $attributeCode = $this->getAttributeCode($value);

            $optionsData = $this->getAttributeOptionsData($entityType, $attributeCode);
            return $optionsData;
        });
    }

    /**
     * Get entity type
     *
     * @param array $value
     * @return int
     * @throws LocalizedException
     */
    private function getEntityType(array $value): int
    {
        if (!isset($value['entity_type'])) {
            throw new LocalizedException(__('"Entity type should be specified'));
        }

        return (int)$value['entity_type'];
    }

    /**
     * Get attribute code
     *
     * @param array $value
     * @return string
     * @throws LocalizedException
     */
    private function getAttributeCode(array $value): string
    {
        if (!isset($value['attribute_code'])) {
            throw new LocalizedException(__('"Attribute code should be specified'));
        }

        return $value['attribute_code'];
    }

    /**
     * Get attribute options data
     *
     * @param int $entityType
     * @param string $attributeCode
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    private function getAttributeOptionsData(int $entityType, string $attributeCode): array
    {
        try {
            $optionsData = $this->attributeOptionsDataProvider->getData($entityType, $attributeCode);
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        } catch (StateException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $optionsData;
    }
}
