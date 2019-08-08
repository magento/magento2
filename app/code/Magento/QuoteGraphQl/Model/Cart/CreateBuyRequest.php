<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

/**
 * Creates buy request that can be used for working with cart items
 */
class CreateBuyRequest
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Returns buy request for working with cart items
     *
     * @param float $qty
     * @param array $customizableOptionsData
     * @return DataObject
     */
    public function execute(float $qty, array $customizableOptionsData): DataObject
    {
        $customizableOptions = [];
        foreach ($customizableOptionsData as $customizableOption) {
            if (isset($customizableOption['value_string'])) {
                $customizableOptions[$customizableOption['id']] = $this->convertCustomOptionValue(
                    $customizableOption['value_string']
                );
            }
        }

        $dataArray = [
            'data' => [
                'qty' => $qty,
                'options' => $customizableOptions,
            ],
        ];
        return $this->dataObjectFactory->create($dataArray);
    }

    /**
     * Convert custom options value
     *
     * @param string $value
     * @return string|array
     */
    private function convertCustomOptionValue(string $value)
    {
        $value = trim($value);
        if (substr($value, 0, 1) === "[" &&
            substr($value, strlen($value) - 1, 1) === "]") {
            return explode(',', substr($value, 1, -1));
        }
        return $value;
    }
}
