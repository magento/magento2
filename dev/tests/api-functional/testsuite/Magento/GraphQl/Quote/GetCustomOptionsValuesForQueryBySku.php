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
            $customOptionsValues[$optionType]['id'] = (int)$customOption->getOptionId();
            switch ($optionType) {
                case 'date':
                    $customOptionsValues[$optionType]['value_string'] = '2012-12-12 00:00:00';
                    break;
                case 'field':
                case 'area':
                    $customOptionsValues[$optionType]['value_string'] = 'test';
                    break;
                case 'drop_down':
                    $optionSelectValues = $customOption->getValues();
                    $customOptionsValues[$optionType]['value_string'] =
                        reset($optionSelectValues)->getOptionTypeId();
                    break;
                case 'multiple':
                    $customOptionsValues[$optionType]['value_string'] =
                        '[' . implode(',', array_keys($customOption->getValues())) . ']';
                    break;
            }
        }
        return $customOptionsValues;
    }
}
