<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Translate;

use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;

/**
 * MethodCollector
 */
class MethodCollector extends PhraseCollector
{
    /**
     * Extract phrases from given tokens. e.g.: __('phrase', ...)
     *
     * @return void
     */
    protected function _extractPhrases()
    {
        if ($this->_tokenizer->getNextToken()->isObjectOperator()) {
            $phraseStartToken = $this->_tokenizer->getNextToken();
            if ($this->_isTranslateFunction($phraseStartToken)) {
                $arguments = $this->_tokenizer->getFunctionArgumentsTokens();
                $phrase = $this->_collectPhrase(array_shift($arguments));
                $this->_addPhrase($phrase, count($arguments), $this->_file, $phraseStartToken->getLine());
            }
        }
    }

    /**
     * Check if token is translated function
     *
     * @param \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Token $token
     * @return bool
     */
    protected function _isTranslateFunction($token)
    {
        return ($token->isEqualFunction(
            '__'
        ) || $token->isWhitespace() && $this->_tokenizer->getNextToken()->isEqualFunction(
            '__'
        )) && $this->_tokenizer->getNextToken()->isOpenBrace();
    }
}
