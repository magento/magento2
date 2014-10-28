<?php
/**
 * Form Element Multiselect Data Model
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

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Metadata\ElementFactory;

class Multiselect extends Select
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(RequestInterface $request)
    {
        $values = $this->_getRequestValue($request);
        if ($values !== false && !is_array($values)) {
            $values = array($values);
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = parent::compactValue($val);
            }

            $value = implode(',', $value);
        }
        return parent::compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->_value;
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (ElementFactory::OUTPUT_FORMAT_ARRAY === $format || ElementFactory::OUTPUT_FORMAT_JSON === $format) {
            return $values;
        }

        $output = array();
        foreach ($values as $value) {
            if (!$value) {
                continue;
            }
            $output[] = $this->_getOptionText($value);
        }

        $output = implode(', ', $output);

        return $output;
    }
}
