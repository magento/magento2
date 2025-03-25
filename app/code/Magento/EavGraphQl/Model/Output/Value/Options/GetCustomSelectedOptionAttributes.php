<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value\Options;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Custom attribute value provider for customer
 */
class GetCustomSelectedOptionAttributes implements GetAttributeSelectedOptionInterface, ResetAfterRequestInterface
{
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var array
     */
    private array $optionsCache = [];

    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritDoc
     *
     * @param string $entity
     * @param string $code
     * @param string $value
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        $result = [];
        $selectedValues = explode(',', $value);
        $options = $this->getAttributeOptions($entity, $code);
        foreach ($selectedValues as $selectedValue) {
            if (isset($options[$selectedValue])) {
                $result[] = [
                    'value' => $selectedValue,
                    'label' => $options[$selectedValue],
                ];
            }
        }

        return $result;
    }

    /**
     * Get cached attribute options
     *
     * @param string $entity
     * @param string $code
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeOptions(string $entity, string $code): array
    {
        $attribute = $this->attributeRepository->get($entity, $code);

        if (!isset($this->optionsCache[$entity][$code])) {
            $options = $attribute->getOptions();
            $optionsLabel = [];
            foreach ($options as $option) {
                $optionsLabel[$option->getValue()] = $option->getLabel();
            }
            $this->optionsCache[$entity][$code] = $optionsLabel;
        }

        return $this->optionsCache[$entity][$code];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->optionsCache = [];
    }
}
