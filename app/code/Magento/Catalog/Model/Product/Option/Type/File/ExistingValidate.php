<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Laminas\Validator\ValidatorChain;

/**
 * Validator for existing (already saved) files.
 */
class ExistingValidate extends ValidatorChain
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @inheritDoc
     *
     * @param string $value File's full path.
     * @param string|null $originalName Original file's name (when uploaded).
     */
    public function isValid($value, $originalName = null)
    {
        $this->messages = [];

        if (!is_string($value)) {
            $this->messages[] = __('Full file path is expected.')->render();
            return false;
        }

        $result = true;
        $fileInfo = null;
        if ($originalName) {
            $fileInfo = ['name' => $originalName, 'tmp_name'=> $value];
        }
        $messagesArray = $errorsArray = [];

        foreach ($this->validators as $element) {
            $validator = $element['instance'];

            if ($validator->isValid($value, $fileInfo)) {
                continue;
            }
            $result = false;
            $messages = $validator->getMessages();
            $messagesArray[] = $messages;
            $errorsArray[] = array_keys($messages);
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        $this->messages = array_merge($this->messages, ...$messagesArray);
        $this->errors = array_merge($this->errors, ...$errorsArray);

        return $result;
    }

    /**
     * Returns array of validation failure message codes
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
