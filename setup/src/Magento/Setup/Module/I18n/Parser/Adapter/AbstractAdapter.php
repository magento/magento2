<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Parser\AdapterInterface;

/**
 * Abstract parser adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Processed file
     *
     * @var string
     */
    protected $_file;

    /**
     * Parsed phrases
     *
     * @var array
     */
    protected $_phrases = [];

    /**
     * {@inheritdoc}
     */
    public function parse($file)
    {
        $this->_phrases = [];
        $this->_file = $file;
        $this->_parse();
    }

    /**
     * Template method
     *
     * @return void
     */
    abstract protected function _parse();

    /**
     * {@inheritdoc}
     */
    public function getPhrases()
    {
        return array_values($this->_phrases);
    }

    /**
     * Add phrase
     *
     * @param string $phrase
     * @param string|int $line
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _addPhrase($phrase, $line = '')
    {
        if (!$phrase) {
            return;
        }
        if (!isset($this->_phrases[$phrase])) {
            $enclosureCharacter = $this->getEnclosureCharacter($phrase);
            if (!empty($enclosureCharacter)) {
                $phrase = $this->trimEnclosure($phrase);
            }

            $this->_phrases[$phrase] = [
                'phrase' => $phrase,
                'file' => $this->_file,
                'line' => $line,
                'quote' => $enclosureCharacter,
            ];
        }
    }

    /**
     * Prepare phrase
     *
     * @param string $phrase
     * @return string
     */
    protected function _stripFirstAndLastChar($phrase)
    {
        return substr($phrase, 1, strlen($phrase) - 2);
    }

    /**
     * Check if first and last char is quote
     *
     * @param string $phrase
     * @return bool
     */
    protected function _isFirstAndLastCharIsQuote($phrase)
    {
        $firstCharacter = $phrase[0];
        $lastCharacter = $phrase[strlen($phrase) - 1];
        return $this->isQuote($firstCharacter) && $firstCharacter == $lastCharacter;
    }

    /**
     * Get enclosing character if any
     *
     * @param string $phrase
     * @return string
     */
    protected function getEnclosureCharacter($phrase)
    {
        $quote = '';
        if ($this->_isFirstAndLastCharIsQuote($phrase)) {
            $quote = $phrase[0];
        }

        return $quote;
    }

    /**
     * @param string $phrase
     * @return string
     */
    protected function trimEnclosure($phrase)
    {
        return $this->_stripFirstAndLastChar($phrase);
    }

    /**
     * @param string $char
     * @return bool
     */
    protected function isQuote($char)
    {
        return in_array($char, [Phrase::QUOTE_DOUBLE, Phrase::QUOTE_SINGLE]);
    }
}
