<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverter;

/**
 *  Convert serialized data in sales tables to JSON
 */
class ConvertSerializedDataToJson
{
    /**
     * @var SalesSetup
     */
    private $salesSetup;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var array
     */
    private $fieldDataConverters = [];

    /**
     * @var array
     */
    private $fieldsToUpdate = [
        [
            'table' => 'sales_order_item',
            'identifier' => 'item_id',
            'title' => 'product_options',
            'data_converter' => SerializedDataConverter::class
        ],
        [
            'table' => 'sales_shipment',
            'identifier' => 'entity_id',
            'title' => 'packages',
            'data_converter' => SerializedToJson::class
        ],
        [
            'table' => 'sales_order_payment',
            'identifier' => 'entity_id',
            'title' => 'additional_information',
            'data_converter' => SerializedToJson::class
        ],
        [
            'table' => 'sales_payment_transaction',
            'identifier' => 'transaction_id',
            'title' => 'additional_information',
            'data_converter' => SerializedToJson::class
        ]
    ];

    /**
     * Constructor
     *
     * @param SalesSetup $salesSetup
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        SalesSetup $salesSetup,
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->salesSetup = $salesSetup;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * Convert data for the following fields from serialized to JSON format:
     * sales_order_item.product_options
     * sales_shipment.packages
     * sales_order_payment.additional_information
     * sales_payment_transaction.additional_information
     *
     * @return void
     */
    public function convert()
    {
        foreach ($this->fieldsToUpdate as $field) {
            $fieldDataConverter = $this->getFieldDataConverter($field['data_converter']);
            $fieldDataConverter->convert(
                $this->salesSetup->getConnection(),
                $this->salesSetup->getTable($field['table']),
                $field['identifier'],
                $field['title']
            );
        }
    }

    /**
     * Get field data converter
     *
     * @param string $dataConverterClassName
     * @return FieldDataConverter
     */
    private function getFieldDataConverter($dataConverterClassName)
    {
        if (!isset($this->fieldDataConverters[$dataConverterClassName])) {
            $this->fieldDataConverters[$dataConverterClassName] = $this->fieldDataConverterFactory->create(
                $dataConverterClassName
            );
        }
        return $this->fieldDataConverters[$dataConverterClassName];
    }
}
