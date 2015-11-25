<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser;

use Magento\Setup\Module\I18n;

/**
 * Contextual Parser
 */
class Contextual extends AbstractParser
{
    /**
     * Context
     *
     * @var \Magento\Setup\Module\I18n\Context
     */
    protected $_context;

    /**
     * Parser construct
     *
     * @param I18n\FilesCollector $filesCollector
     * @param I18n\Factory $factory
     * @param I18n\Context $context
     */
    public function __construct(I18n\FilesCollector $filesCollector, I18n\Factory $factory, I18n\Context $context)
    {
        $this->_context = $context;

        parent::__construct($filesCollector, $factory);
    }

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

            list($contextType, $contextValue) = $this->_context->getContextByPath($file);

            foreach ($adapter->getPhrases() as $phraseData) {
                $this->_addPhrase($phraseData, $contextType, $contextValue);
            }
        }
    }

    /**
     * Add phrase with context
     *
     * @param array $phraseData
     * @param string $contextType
     * @param string $contextValue
     * @return void
     */
    protected function _addPhrase($phraseData, $contextType, $contextValue)
    {
        $phraseKey = $contextType . $contextValue. stripslashes($phraseData['phrase']);

        if (isset($this->_phrases[$phraseKey])) {
            /** @var \Magento\Setup\Module\I18n\Dictionary\Phrase $phrase */
            $phrase = $this->_phrases[$phraseKey];
            $phrase->addContextValue($contextValue);
        } else {
            $this->_phrases[$phraseKey] = $this->_factory->createPhrase(
                [
                    'phrase' => $phraseData['phrase'],
                    'translation' => $phraseData['phrase'],
                    'context_type' => $contextType,
                    'context_value' => [$contextValue],
                    'quote' => $phraseData['quote'],
                ]
            );
        }
    }
}
