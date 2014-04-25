<?php
/**
 * No such entity service exception
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
