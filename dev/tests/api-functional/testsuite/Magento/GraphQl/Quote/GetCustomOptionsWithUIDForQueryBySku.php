<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;

/**
 * Generate an array with test values for customizable options with UID
 */
class GetCustomOptionsWithUIDForQueryBySku
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
        $selectedOptions = [];
        $enteredOptions = [];

        foreach ($customOptions as $customOption) {
            $optionType = $customOption->getType();

            switch ($optionType) {
                case 'field':
                case 'area':
                    $enteredOptions[] = [
                        'type' => 'field',
                        'uid' => $this->encodeEnteredOption((int) $customOption->getOptionId()),
                        'value' => 'test'
                    ];
                    break;
                case 'date':
                    $enteredOptions[] = [
                        'type' => 'date',
                        'uid' => $this->encodeEnteredOption((int) $customOption->getOptionId()),
                        'value' => '2012-12-12 00:00:00'
                    ];
                    break;
                case 'drop_down':
                    $optionSelectValues = $customOption->getValues();
                    $selectedOptions[] = $this->encodeSelectedOption(
                        (int) $customOption->getOptionId(),
                        (int) reset($optionSelectValues)->getOptionTypeId()
                    );
                    break;
                case 'multiple':
                    foreach ($customOption->getValues() as $optionValue) {
                        $selectedOptions[] = $this->encodeSelectedOption(
                            (int) $customOption->getOptionId(),
                            (int) $optionValue->getOptionTypeId()
                        );
                    }
                    break;
            }
        }

        return [
            'selected_options' => $selectedOptions,
            'entered_options' => $enteredOptions
        ];
    }

    /**
     * Returns UID of the selected custom option
     *
     * @param int $optionId
     * @param int $optionValueId
     * @return string
     */
    private function encodeSelectedOption(int $optionId, int $optionValueId): string
    {
        return base64_encode("custom-option/$optionId/$optionValueId");
    }

    /**
     * Returns UID of the entered custom option
     *
     * @param int $optionId
     * @return string
     */
    private function encodeEnteredOption(int $optionId): string
    {
        return base64_encode("custom-option/$optionId");
    }
}
