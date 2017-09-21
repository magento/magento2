<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

/**
 * Utility methods for converters
 */
class ConverterUtils
{
    /**
     * Retrieve component name
     *
     * @param \DOMNode $node
     * @return string
     */
    public function getComponentName(\DOMNode $node)
    {
        $result = $node->localName;
        if (!$node->hasAttributes()) {
            return $result;
        }
        foreach ($node->attributes as $attribute) {
            if ($attribute->name == Converter::NAME_ATTRIBUTE_KEY) {
                $result = $attribute->value;
                break;
            }
        }

        return $result;
    }

    /**
     * Check that $node is UiComponent
     *
     * If $node has 'settings', 'formElements' node in any parent node that it is not UiComponent
     *
     * @param \DOMNode $node
     * @return boolean
     */
    public function isUiComponent(\DOMNode $node)
    {
        if (in_array($node->localName, [Converter::SETTINGS_KEY, 'formElements'])) {
            return false;
        } elseif ($node->parentNode !== null) {
            return $this->isUiComponent($node->parentNode);
        }

        return true;
    }
}
