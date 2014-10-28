<?php
/**
 * Form Element Multiline Data Model
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
namespace Magento\Customer\Model\Metadata\Form;

class Multiline extends Text
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        if (!is_array($value)) {
            $value = false;
        } else {
            $value = array_map(array($this, '_applyInputFilter'), $value);
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        $errors = array();
        $attribute = $this->getAttribute();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
            if (!is_array($value)) {
                $value = explode("\n", $value);
            }
        }

        if (!is_array($value)) {
            $value = array($value);
        }
        for ($i = 0; $i < $attribute->getMultilineCount(); $i++) {
            if (!isset($value[$i])) {
                $value[$i] = null;
            }
            // validate first line
            if ($i == 0) {
                $result = parent::validateValue($value[$i]);
                if ($result !== true) {
                    $errors = $result;
                }
            } else {
                if (!empty($value[$i])) {
                    $result = parent::validateValue($value[$i]);
                    if ($result !== true) {
                        $errors = array_merge($errors, $result);
                    }
                }
            }
        }

        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        return parent::compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->_value;
        if (!is_array($values)) {
            $values = explode("\n", $values);
        }
        $values = array_map(array($this, '_applyOutputFilter'), $values);
        switch ($format) {
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_ARRAY:
                $output = $values;
                break;
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_HTML:
                $output = implode("<br />", $values);
                break;
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_ONELINE:
                $output = implode(" ", $values);
                break;
            default:
                $output = implode("\n", $values);
                break;
        }
        return $output;
    }
}
