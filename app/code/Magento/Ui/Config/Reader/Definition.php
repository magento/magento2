<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader;

use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * UI Component definition config reader
 */
class Definition extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * Read, merge configuration files and validate resulted XML
     *
     * @param array $fileList
     * @return array
     * @throws LocalizedException if XML file is invalid
     */
    protected function _readFiles($fileList)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                if (!$configMerger) {
                    $configMerger = $this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (ValidationException $e) {
                throw new LocalizedException(
                    new Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }
        if ($this->validationState->isValidationRequired()) {
            $errors = [];
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new LocalizedException(
                    new Phrase($message . implode("\n", $errors))
                );
            }
        }

        $output = [];
        if ($configMerger) {
            $output = $this->_converter->convert($configMerger->getDom()->documentElement);
        }
        return $output;
    }
}
