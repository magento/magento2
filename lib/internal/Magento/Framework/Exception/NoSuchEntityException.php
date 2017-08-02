<?php
/**
 * No such entity service exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 2.0.0
 */
class NoSuchEntityException extends LocalizedException
{
    /**
     * @deprecated
     */
    const MESSAGE_SINGLE_FIELD = 'No such entity with %fieldName = %fieldValue';

    /**
     * @deprecated
     */
    const MESSAGE_DOUBLE_FIELDS = 'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @since 2.0.0
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('No such entity.');
        }
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Helper function for creating an exception when a single field is responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public static function singleField($fieldName, $fieldValue)
    {
        return new self(
            new Phrase(
                'No such entity with %fieldName = %fieldValue',
                [
                    'fieldName' => $fieldName,
                    'fieldValue' => $fieldValue
                ]
            )
        );
    }

    /**
     * Helper function for creating an exception when two fields are responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @param string $secondFieldName
     * @param string|int $secondFieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public static function doubleField($fieldName, $fieldValue, $secondFieldName, $secondFieldValue)
    {
        return new self(
            new Phrase(
                'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                [
                    'fieldName' => $fieldName,
                    'fieldValue' => $fieldValue,
                    'field2Name' => $secondFieldName,
                    'field2Value' => $secondFieldValue,
                ]
            )
        );
    }
}
