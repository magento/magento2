<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Pack\Writer\File;

/**
 * Pack writer csv
 */
class Csv extends AbstractFile
{
    /**
     * File extension
     */
    const FILE_EXTENSION = 'csv';

    /**
     * {@inheritdoc}
     */
    public function _writeFile($file, $phrases)
    {
        if (self::MODE_MERGE == $this->_mode) {
            $phrases = $this->_mergeDictionaries($file, $phrases);
        }

        $writer = $this->_factory->createDictionaryWriter($file);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Phrase $phrase */
        foreach ($phrases as $phrase) {
            $phrase->setContextType(null);
            $phrase->setContextValue(null);

            $writer->write($phrase);
        }
    }

    /**
     * Merge dictionaries
     *
     * @param string $file
     * @param array $phrases
     * @return array
     */
    protected function _mergeDictionaries($file, $phrases)
    {
        if (!file_exists($file)) {
            return $phrases;
        }
        $dictionary = $this->_dictionaryLoader->load($file);

        $merged = [];
        foreach ($dictionary->getPhrases() as $phrase) {
            $merged[$phrase->getPhrase()] = $phrase;
        }
        /** @var \Magento\Setup\Module\I18n\Dictionary\Phrase $phrase */
        foreach ($phrases as $phrase) {
            $merged[$phrase->getPhrase()] = $phrase;
        }
        return $merged;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
}
