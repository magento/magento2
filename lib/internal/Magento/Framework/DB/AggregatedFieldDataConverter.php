<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Date converter for multiple fields in different tables using different field converters
 * @since 2.2.0
 */
class AggregatedFieldDataConverter
{
    /**
     * @var FieldDataConverterFactory
     * @since 2.2.0
     */
    private $fieldDataConverterFactory;

    /**
     * @var FieldDataConverter[]
     * @since 2.2.0
     */
    private $fieldDataConverters = [];

    /**
     * Constructor
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
