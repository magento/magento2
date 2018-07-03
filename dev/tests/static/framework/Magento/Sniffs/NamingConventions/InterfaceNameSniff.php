<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class InterfaceNameSniff implements Sniff
{
    const INTERFACE_SUFFIX = 'Interface';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_INTERFACE];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        $declarationLine = $tokens[$stackPtr]['line'];
        $suffixLength = strlen(self::INTERFACE_SUFFIX);
        // Find first T_STRING after 'interface' keyword in the line and verify it
        while ($tokens[$stackPtr]['line'] == $declarationLine) {
            if ($tokens[$stackPtr]['type'] == 'T_STRING') {
                if (substr($tokens[$stackPtr]['content'], 0 - $suffixLength) != self::INTERFACE_SUFFIX) {
                    $sourceFile->addError(
                        'Interface should have name that ends with "Interface" suffix.',
                        $stackPtr,
                        'WrongInterfaceName'
                    );
                }
                break;
            }
            $stackPtr++;
        }
    }
}
