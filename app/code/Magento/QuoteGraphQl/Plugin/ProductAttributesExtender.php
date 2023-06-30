<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Validator\Attribute\Code;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Fields;
use Magento\Framework\Validator\ValidateException;
use Magento\Quote\Model\Quote\Config as QuoteConfig;

/**
 * Class for extending product attributes for quote.
 */
class ProductAttributesExtender
{
    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var Code
     */
    private Code $attributeCodeValidator;

    /**
     * @param Fields $fields
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Code|null $attributeCodeValidator
     */
    public function __construct(
        Fields $fields,
        AttributeCollectionFactory $attributeCollectionFactory,
        Code $attributeCodeValidator = null
    ) {
        $this->fields = $fields;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeCodeValidator = $attributeCodeValidator ?? ObjectManager::getInstance()->get(Code::class);
    }

    /**
     * Get only attribute code that pass validation
     *
     * @return array
     */
    private function getValidatedAttributeCodes(): array
    {
        return array_filter($this->fields->getFieldsUsedInQuery(), [$this,'validateAttributeCode']);
    }

    /**
     * @param string|int $code
     * @return bool
     * @throws ValidateException
     */
    private function validateAttributeCode(string|int $code)
    {
        return $this->attributeCodeValidator->isValid((string)$code);
    }

    /**
     * Add requested product attributes.
     *
     * @param QuoteConfig $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(QuoteConfig $subject, array $result): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect('attribute_code')
            ->setCodeFilter($this->getValidatedAttributeCodes())
            ->load();
        $attributes = $attributeCollection->getColumnValues('attribute_code');

        return array_unique(array_merge($result, $attributes));
    }
}
