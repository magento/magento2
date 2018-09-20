<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * Validator for existing (already saved) files.
 */
class ExistingValidate extends \Zend_Validate
{
    /**
     * @inheritDoc
     *
     * @param string $value File's full path.
     * @param string|null $originalName Original file's name (when uploaded).
     */
    public function isValid($value, $originalName = null)
    {
        $this->_messages = [];
        $this->_errors = [];

        if (!is_string($value)) {
            $this->_messages[] = __('Full file path is expected.')->render();
            return false;
        }

        $result = true;
        $fileInfo = null;
        if ($originalName) {
            $fileInfo = ['name' => $originalName];
        }
        foreach ($this->_validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value, $fileInfo)) {
                continue;
            }
            $result = false;
            $messages = $validator->getMessages();
            $this->_messages = array_merge($this->_messages, $messages);
            $this->_errors = array_merge($this->_errors, array_keys($messages));
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }
}
