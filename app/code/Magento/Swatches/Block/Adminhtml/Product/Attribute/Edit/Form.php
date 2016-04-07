<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Swatches\Model\Swatch;

/**
 * Class Form
 */
class Form extends \Magento\Framework\Data\Form
{
    /**
     * @param array $values
     * @return $this
     */
    public function addValues($values)
    {
        if (!is_array($values)) {
            return $this;
        }
        $values = array_merge(
            $values,
            $this->getAdditionalData($values)
        );
        if (isset($values['frontend_input']) && 'select' == $values['frontend_input']
            && isset($values[Swatch::SWATCH_INPUT_TYPE_KEY])
        ) {
            $values['frontend_input'] = 'swatch_' . $values[Swatch::SWATCH_INPUT_TYPE_KEY];
        }

        return parent::addValues($values);
    }

    /**
     * @param array $values
     * @return array
     */
    protected function getAdditionalData(array $values)
    {
        $additionalData = [];
        if (isset($values['additional_data'])) {
            $additionalData = unserialize($values['additional_data']);
            if (!is_array($additionalData)) {
                $additionalData = [];
            }
        }

        return $additionalData;
    }
}
