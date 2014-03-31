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
namespace Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Translate;

use Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\PhraseCollector;

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
     * @param \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\Token $token
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
