<?php
/**
 * No such entity service exception
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class NoSuchEntityException extends \Magento\Framework\Exception\LocalizedException
{
    const MESSAGE_SINGLE_FIELD = 'No such entity with %fieldName = %fieldValue';
    const MESSAGE_DOUBLE_FIELDS = 'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value';

    /**
     * @param string $message
     * @param array $params
     * @param \Exception $cause
     */
    public function __construct(
        $message = 'No such entity.',
        array $params = [],
        \Exception $cause = null
    ) {
        parent::__construct($message, $params, $cause);
    }

    /**
     * Helper function for creating an exception when a single field is responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @return NoSuchEntityException
     */
    public static function singleField($fieldName, $fieldValue)
    {
        return new NoSuchEntityException(
            self::MESSAGE_SINGLE_FIELD,
            [
                'fieldName' => $fieldName,
                'fieldValue' => $fieldValue,
            ]
        );
    }

    /**
     * Helper function for creating an exception when two fields are responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @param string $secondFieldName
     * @param string|int $secondFieldValue
     * @return NoSuchEntityException
     */
    public static function doubleField($fieldName, $fieldValue, $secondFieldName, $secondFieldValue)
    {
        return new NoSuchEntityException(
            self::MESSAGE_DOUBLE_FIELDS,
            [
                'fieldName' => $fieldName,
                'fieldValue' => $fieldValue,
                'field2Name' => $secondFieldName,
                'field2Value' => $secondFieldValue,
            ]
        );
    }
}
