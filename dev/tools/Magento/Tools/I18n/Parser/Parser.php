<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n\Parser;

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
        $phraseKey = $phraseData['phrase'];

        $this->_phrases[$phraseKey] = $this->_factory->createPhrase([
            'phrase'      => $phraseData['phrase'],
            'translation' => $phraseData['phrase'],
            'quote'       => $phraseData['quote'],
        ]);
    }
}
