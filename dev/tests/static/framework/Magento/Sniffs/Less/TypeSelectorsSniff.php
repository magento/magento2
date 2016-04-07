<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class TypeSelectorsSniff
 *
 * Ensure that Type selectors respond to the following requirements:
 *
 * - avoid qualifying class names with type selectors;
 * - Unless necessary (for example with helper classes), do not use element names in conjunction with IDs or classes.
 * - Type selectors must be lowercase
 * - Write selector in one line, do not use concatenation
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#selectors-naming
 *
 */
class TypeSelectorsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Tags that are not allowed as type selector
     *
     * @var array
     */
    private $tags = [
        'a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
        'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
        'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
        'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
        'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
        'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
        'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
        'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
        'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
        'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
        'tr', 'tt', 'ul', 'var',
            // HTML5
        'article', 'aside', 'audio', 'bdi', 'canvas', 'command',
        'datalist', 'details', 'dialog', 'embed', 'figure', 'figcaption',
        'footer', 'header', 'hgroup', 'keygen', 'mark', 'meter', 'nav',
        'output', 'progress', 'ruby', 'rt', 'rp', 'track', 'section',
        'source', 'summary', 'time', 'video', 'wbr'
    ];

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_STRING_CONCAT];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $bracketPtr = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr);

        if (false === $bracketPtr) {
            return;
        }

        $isBracketOnSameLane = (bool)($tokens[$bracketPtr]['line'] === $tokens[$stackPtr]['line']);

        if (!$isBracketOnSameLane) {
            return;
        }

        if ((T_STRING === $tokens[$stackPtr - 1]['code'])
            && in_array($tokens[$stackPtr - 1]['content'], $this->tags)
        ) {
            // Will be implemented in MAGETWO-49778
            //$phpcsFile->addWarning('Type selector is used', $stackPtr, 'TypeSelector');
        }

        for ($i = $stackPtr; $i < $bracketPtr; $i++) {
            if (preg_match('/[A-Z]/', $tokens[$i]['content'])) {
                $phpcsFile->addError('Selector contains uppercase symbols', $stackPtr, 'UpperCaseSelector');
            }
        }
    }
}
