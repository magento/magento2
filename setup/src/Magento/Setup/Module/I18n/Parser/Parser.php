<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser;

/**
 * Parser
 */
class Parser extends AbstractParser
{
    /**
     * Parse one type
     *
     * @param array $options
     * @return void
     */
    protected function _parseByTypeOptions($options)
    {
        foreach ($this->_getFiles($options) as $file) {
            $adapter = $this->_adapters[$options['type']];
            $adapter->parse($file);

            foreach ($adapter->getPhrases() as $phraseData) {
                $this->_addPhrase($phraseData);
            }
        }
    }

    /**
     * Add phrase
     *
     * @param array $phraseData
     * @return void
     */
    protected function _addPhrase($phraseData)
    {
        try {
            $phrase = $this->_factory->createPhrase([
                'phrase' => $phraseData['phrase'],
                'translation' => $phraseData['phrase'],
                'quote' => $phraseData['quote'],
            ]);
            $this->_phrases[$phrase->getCompiledPhrase()] = $phrase;
        } catch (\DomainException $e) {
            throw new \DomainException(
                "{$e->getMessage()} in {$phraseData['file']}:{$phraseData['line']}",
                $e->getCode(),
                $e
            );
        }
    }
}
