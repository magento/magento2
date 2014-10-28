<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer;

use Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer;

/**
 * PhraseCollector
 */
class PhraseCollector
{
    /**
     * @var \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer
     */
    protected $_tokenizer;

    /**
     * Phrases
     *
     * @var array
     */
    protected $_phrases = array();

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
        $this->_phrases = array();
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
        $phrase = array();
        if ($phraseTokens) {
            /** @var \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Token $phraseToken */
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
        $this->_phrases[] = array(
            'phrase' => $phrase,
            'arguments' => $argumentsAmount,
            'file' => $file,
            'line' => $line
        );
    }
}
