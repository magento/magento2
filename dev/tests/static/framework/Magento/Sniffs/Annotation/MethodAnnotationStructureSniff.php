<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sniffs\Annotation;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff to validate structure of public, private, protected method annotations
 */
class MethodAnnotationStructureSniff implements Sniff
{
    /**
     * @var AnnotationFormatValidator
     */
    private $annotationFormatValidator;

    /**
     * AnnotationStructureSniff constructor.
     */
    public function __construct()
    {
        $this->annotationFormatValidator = new AnnotationFormatValidator();
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_FUNCTION
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $commentStartPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, ($stackPtr), 0);
        $commentEndPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, ($stackPtr), 0);
        $commentCloserPtr = $tokens[$commentStartPtr]['comment_closer'];
        $functionPtrContent = $tokens[$stackPtr+2]['content'] ;
        if (preg_match('/(?i)__construct/', $functionPtrContent)) {
            return;
        }
        $emptyTypeTokens = [
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR
        ];
        $shortPtr = $phpcsFile->findNext($emptyTypeTokens, $commentStartPtr + 1, $commentCloserPtr, true);
        if ($shortPtr === false) {
            $error = 'Annotation block is empty';
            $phpcsFile->addError($error, $commentStartPtr, 'MethodAnnotation');
        } else {
            $this->annotationFormatValidator->validateDescriptionFormatStructure(
                $phpcsFile,
                $commentStartPtr,
                (int) $shortPtr,
                $commentEndPtr,
                $emptyTypeTokens
            );
            if (empty($tokens[$commentStartPtr]['comment_tags'])) {
                return;
            }
            $this->annotationFormatValidator->validateTagsSpacingFormat(
                $phpcsFile,
                $commentStartPtr,
                $emptyTypeTokens
            );
            $this->annotationFormatValidator->validateTagGroupingFormat($phpcsFile, $commentStartPtr);
            $this->annotationFormatValidator->validateTagAligningFormat($phpcsFile, $commentStartPtr);
        }
    }
}
