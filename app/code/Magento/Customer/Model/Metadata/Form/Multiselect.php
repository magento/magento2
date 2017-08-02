<?php
/**
 * Form Element Multiselect Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Customer\Model\Metadata\Form\Multiselect
 *
 * @since 2.0.0
 */
class Multiselect extends Select
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function extractValue(RequestInterface $request)
    {
        $values = $this->_getRequestValue($request);
        if ($values !== false && !is_array($values)) {
            $values = [$values];
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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

        $output = [];
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
