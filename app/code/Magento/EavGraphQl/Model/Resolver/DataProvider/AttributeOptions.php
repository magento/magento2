<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\DataProvider;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Query\Uid;

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
     * @var Uid
     */
    private $idEncoder;

    /**
     * @param AttributeOptionManagementInterface $optionManager
     * @param Uid $idEncoder
     */
    public function __construct(
        AttributeOptionManagementInterface $optionManager,
        Uid $idEncoder
    ) {
        $this->optionManager = $optionManager;
        $this->idEncoder = $idEncoder;
    }

    /**
     * Get attribute options data
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return array
     * @throws InputException
     * @throws StateException
     */
    public function getData(string $entityType, string $attributeCode): array
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
                'value' => $option->getValue(),
                'uid' => $this->idEncoder->encode($option->getId())
            ];
        }
        return $optionsData;
    }
}
