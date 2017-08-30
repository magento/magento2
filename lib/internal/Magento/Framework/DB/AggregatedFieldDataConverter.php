<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Date converter for multiple fields in different tables using different field converters
 */
class AggregatedFieldDataConverter
{
    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var FieldDataConverter[]
     */
    private $fieldDataConverters = [];

    /**
     * Constructor
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * Convert data for the specified fields using specified field converters
     *
     * @param FieldToConvert[] $fieldsToUpdate
     * @param AdapterInterface $connection
     * @throws FieldDataConversionException
     * @return void
     */
    public function convert(array $fieldsToUpdate, AdapterInterface $connection)
    {
        foreach ($fieldsToUpdate as $field) {
            $fieldDataConverter = $this->getFieldDataConverter($field->getDataConverterClass());
            $fieldDataConverter->convert(
                $connection,
                $field->getTableName(),
                $field->getIdentifierField(),
                $field->getFieldName(),
                $field->getQueryModifier()
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
