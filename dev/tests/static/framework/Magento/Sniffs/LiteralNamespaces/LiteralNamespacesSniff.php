<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\LiteralNamespaces;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Custom phpcs sniff to detect usages of literal class and interface names.
 */
class LiteralNamespacesSniff implements Sniff
{
    /**
     * @var string
     */
    private $literalNamespacePattern = '/^[\\\]{0,2}[A-Z][A-Za-z]+([\\\]{1,2}[A-Z][A-Za-z]+){2,}(?!\\\+)$/';

    /**
     * @var array
     */
    private $classNames = [];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_DOUBLE_QUOTED_STRING,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        if ($sourceFile->findPrevious(T_STRING_CONCAT, $stackPtr, $stackPtr - 3) ||
            $sourceFile->findNext(T_STRING_CONCAT, $stackPtr, $stackPtr + 3)
        ) {
            return;
        }

        $content = trim($tokens[$stackPtr]['content'], "\"'");
        // replace double slashes from class name for avoiding problems with class autoload
        if (strpos($content, '\\') !== false) {
            $content = preg_replace('|\\\{2,}|', '\\', $content);
        }

        if (preg_match($this->literalNamespacePattern, $content) === 1 && $this->classExists($content)) {
            $sourceFile->addError(
                "Use ::class notation instead.",
                $stackPtr,
                'LiteralClassUsage'
            );
        }
    }

    /**
     * @param string $className
     * @return bool
     */
    private function classExists($className)
    {
        if (!isset($this->classNames[$className])) {
            $this->classNames[$className] = class_exists($className) || interface_exists($className);
        }
        return $this->classNames[$className];
    }
}
