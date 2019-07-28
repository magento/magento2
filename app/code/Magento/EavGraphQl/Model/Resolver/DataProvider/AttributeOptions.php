<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\DataProvider;

use Magento\Eav\Api\AttributeOptionManagementInterface;

/**
 * Attribute Options data provider
 */
class AttributeOptions
{
    /**
     * @var AttributeOptionManagementInterface
     */
    private $optionManager;

    /**
     * @param AttributeOptionManagementInterface $optionManager
     */
    public function __construct(
        AttributeOptionManagementInterface $optionManager
    ) {
        $this->optionManager = $optionManager;
    }

    /**
     * @param int $entityType
     * @param string $attributeCode
     * @return array
     */
    public function getData(int $entityType, string $attributeCode): array
    {
        $options = $this->optionManager->getItems($entityType, $attributeCode);

        $optionsData = [];
        foreach ($options as $option) {
            // without empty option @see \Magento\Eav\Model\Entity\Attribute\Source\Table::getAllOptions
            if ($option->getValue() === '') {
                continue;
            }

            $optionsData[] = [
                'label' => $option->getLabel(),
                'value' => $option->getValue()
            ];
        }
        return $optionsData;
    }
}
