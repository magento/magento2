<?php
/**
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
namespace Magento\Framework\Data;

/**
 * Interface SearchResultProcessorInterface
 */
interface SearchResultProcessorInterface
{
    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function getAllIds();

    /**
     * Get current collection page
     *
     * @return int
     */
    public function getCurrentPage();

    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Retrieve collection first item
     *
     * @return \Magento\Framework\Object
     */
    public function getFirstItem();

    /**
     * Retrieve collection last item
     *
     * @return \Magento\Framework\Object
     */
    public function getLastItem();

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function getColumnValues($colName);

    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function getItemsByColumnValue($column, $value);

    /**
     * Search first item by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\Object || null
     */
    public function getItemByColumnValue($column, $value);

    /**
     * Retrieve item by id
     *
     * @param   mixed $idValue
     * @return  \Magento\Framework\Object
     */
    public function getItemById($idValue);

    /**
     * Walk through the collection and run model method or external callback
     * with optional arguments
     *
     * Returns array with results of callback for each item
     *
     * @param string $callback
     * @param array $arguments
     * @return array
     */
    public function walk($callback, array $arguments = []);

    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function toXml();

    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     * @return array
     */
    public function toArray($arrRequiredFields = []);

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    public function toOptionArray($valueField = null, $labelField = null, $additional = []);

    /**
     * Convert items array to hash for select options
     *
     * return items hash
     * array($value => $label)
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    public function toOptionHash($valueField, $labelField);
}
