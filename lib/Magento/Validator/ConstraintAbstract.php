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
 * @category    Magento
 * @package     Magento_Validator
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Validator constraint interface.
 */
abstract class Magento_Validator_ConstraintAbstract
{
    protected $_errors = array();

    /**
     * Validate field value in data.
     *
     * @param array $data
     * @param string $field
     * @return boolean
     */
    abstract public function isValidData(array $data, $field = null);

    /**
     * Get constraint error messages.
     * Errors should be stored in associative array grouped by field name, e.g.
     * array(
     *     'field_name_1' => array(
     *          'Error message #1',
     *          'Error message #2',
     *          ...
     *      ),
     *      'field_name_2' => array(
     * )
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Add error message
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message)
    {
        $this->_errors[$field][] = $message;
    }
}
