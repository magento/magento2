<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value\Options;

use Magento\Eav\Model\AttributeRepository;

/**
 * Custom attribute value provider for customer
 */
class GetCustomSelectedOptionAttributes implements GetAttributeSelectedOptionInterface
{
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        $attribute = $this->attributeRepository->get($entity, $code);

        $result = [];
        $selectedValues = explode(',', $value);
        foreach ($attribute->getOptions() as $option) {
            if (!in_array($option->getValue(), $selectedValues)) {
                continue;
            }
            $result[] = [
                'value' => $option->getValue(),
                'label' => $option->getLabel()
            ];
        }
        return $result;
    }
}
