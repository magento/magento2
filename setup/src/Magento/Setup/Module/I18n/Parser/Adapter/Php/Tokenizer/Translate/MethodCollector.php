<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;

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
        $token = $this->_tokenizer->getNextRealToken();
        if ($token && $token->isObjectOperator()) {
            $phraseStartToken = $this->_tokenizer->getNextRealToken();
            if ($phraseStartToken && $this->_isTranslateFunction($phraseStartToken)) {
                $arguments = $this->_tokenizer->getFunctionArgumentsTokens();
                $phrase = $this->_collectPhrase(array_shift($arguments));
                $this->_addPhrase($phrase, count($arguments), $this->_file, $phraseStartToken->getLine());
            }
        }
    }

    /**
     * Check if token is translated function
     *
     * @param \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token $token
     * @return bool
     */
    protected function _isTranslateFunction($token)
    {
        $nextToken = $this->_tokenizer->getNextRealToken();
        return $nextToken && $token->isEqualFunction('__') && $nextToken->isOpenBrace();
    }
}
