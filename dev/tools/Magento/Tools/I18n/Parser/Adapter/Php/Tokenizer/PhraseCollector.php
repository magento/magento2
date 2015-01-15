<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;

/**
 * PhraseCollector
 */
class PhraseCollector
{
    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer
     */
    protected $_tokenizer;

    /**
     * Phrases
     *
     * @var array
     */
    protected $_phrases = [];

    /**
     * Processed file
     *
     * @var \SplFileInfo
     */
    protected $_file;

    /**
     * Construct
     *
     * @param Tokenizer $tokenizer
     */
    public function __construct(Tokenizer $tokenizer)
    {
        $this->_tokenizer = $tokenizer;
    }

    /**
     * Get phrases for given file
     *
     * @return array
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }

    /**
     * Parse given files for phrase
     *
     * @param string $file
     * @return void
     */
    public function parse($file)
    {
        $this->_phrases = [];
        $this->_file = $file;
        $this->_tokenizer->parse($file);
        while (!$this->_tokenizer->isLastToken()) {
            $this->_extractPhrases();
        }
    }

    /**
     * Extract phrases from given tokens. e.g.: __('phrase', ...)
     *
     * @return void
     */
    protected function _extractPhrases()
    {
        $phraseStartToken = $this->_tokenizer->getNextToken();
        if ($phraseStartToken->isEqualFunction('__') && $this->_tokenizer->getNextToken()->isOpenBrace()) {
            $arguments = $this->_tokenizer->getFunctionArgumentsTokens();
            $phrase = $this->_collectPhrase(array_shift($arguments));
            if (null !== $phrase) {
                $this->_addPhrase($phrase, count($arguments), $this->_file, $phraseStartToken->getLine());
            }
        }
    }

    /**
     * Collect all phrase parts into string. Return null if phrase is a variable
     *
     * @param array $phraseTokens
     * @return string|null
     */
    protected function _collectPhrase($phraseTokens)
    {
        $phrase = [];
        if ($phraseTokens) {
            /** @var \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Token $phraseToken */
            foreach ($phraseTokens as $phraseToken) {
                if ($phraseToken->isConstantEncapsedString()) {
                    $phrase[] = $phraseToken->getValue();
                }
            }
            if ($phrase) {
                return implode(' ', $phrase);
            }
        }
        return null;
    }

    /**
     * Add phrase
     *
     * @param string $phrase
     * @param int $argumentsAmount
     * @param \SplFileInfo $file
     * @param int $line
     * @return void
     */
    protected function _addPhrase($phrase, $argumentsAmount, $file, $line)
    {
        $this->_phrases[] = [
            'phrase' => $phrase,
            'arguments' => $argumentsAmount,
            'file' => $file,
            'line' => $line,
        ];
    }
}
