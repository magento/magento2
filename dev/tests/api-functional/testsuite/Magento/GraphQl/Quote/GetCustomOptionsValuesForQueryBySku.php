<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;

/**
 * Generate an array with test values for customizable options based on the option type
 */
class GetCustomOptionsValuesForQueryBySku
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionRepository;

    /**
     * @param ProductCustomOptionRepositoryInterface $productCustomOptionRepository
     */
    public function __construct(ProductCustomOptionRepositoryInterface $productCustomOptionRepository)
    {
        $this->productCustomOptionRepository = $productCustomOptionRepository;
    }

    /**
     * Returns array of custom options for the product
     *
     * @param string $sku
     * @return array
     */
    public function execute(string $sku): array
    {
        $customOptions = $this->productCustomOptionRepository->getList($sku);
        $customOptionsValues = [];

        foreach ($customOptions as $customOption) {
            $optionType = $customOption->getType();
            if ($optionType == 'field' || $optionType == 'area') {
                $customOptionsValues[] = [
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => 'test'
                ];
            } elseif ($optionType == 'drop_down') {
                $optionSelectValues = $customOption->getValues();
                $customOptionsValues[] = [
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => reset($optionSelectValues)->getOptionTypeId()
                ];
            } elseif ($optionType == 'multiple') {
                $customOptionsValues[] = [
                    'id' => (int)$customOption->getOptionId(),
                    'value_string' => '[' . implode(',', array_keys($customOption->getValues())) . ']'
                ];
            }
        }

        return $customOptionsValues;
    }
}
