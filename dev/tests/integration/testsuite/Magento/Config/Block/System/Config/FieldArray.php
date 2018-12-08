<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\Config\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer for integration test.
 */
class FieldArray extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $value = '';
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            $value = implode('|', $element->getValue());
        }

        return sprintf(
            '<input id="%s" name="%s" value="%s" />',
            $element->getId(),
            $element->getName(),
            $value
        );
    }
}
