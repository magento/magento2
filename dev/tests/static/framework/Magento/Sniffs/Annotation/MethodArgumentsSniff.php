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
 * Sniff to validate method arguments annotations
 */
class MethodArgumentsSniff implements Sniff
{
    /**
     * @var array
     */
    private $validTokensBeforeClosingCommentTag = [
        'T_WHITESPACE',
        'T_PUBLIC',
        'T_PRIVATE',
        'T_PROTECTED',
        'T_STATIC',
        'T_ABSTRACT',
        'T_FINAL'
    ];

    /**
     * @var array
     */
    private $invalidTypes = [
        'null',
        'false',
        'true',
        'self'
    ];

    /**
     * @inheritdoc
     */
    public function register() : array
    {
        return [
            T_FUNCTION
        ];
    }

    /**
     * Validates whether valid token exists before closing comment tag
     *
     * @param string $type
     * @return bool
     */
    private function isTokenBeforeClosingCommentTagValid(string $type) : bool
    {
        return in_array($type, $this->validTokensBeforeClosingCommentTag);
    }

    /**
     * Validates whether comment block exists
     *
     * @param File $phpcsFile
     * @param int $previousCommentClosePtr
     * @param int $stackPtr
     * @return bool
     */
    private function validateCommentBlockExists(File $phpcsFile, int $previousCommentClosePtr, int $stackPtr) : bool
    {
        $tokens = $phpcsFile->getTokens();
        for ($tempPtr = $previousCommentClosePtr + 1; $tempPtr < $stackPtr; $tempPtr++) {
            if (!$this->isTokenBeforeClosingCommentTagValid($tokens[$tempPtr]['type'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks whether the parameter type is invalid
     *
     * @param string $type
     * @return bool
     */
    private function isInvalidType(string $type) : bool
    {
        return in_array(strtolower($type), $this->invalidTypes);
    }

    /**
     * Get arguments from method signature
     *
     * @param File $phpcsFile
     * @param int $openParenthesisPtr
     * @param int $closedParenthesisPtr
     * @return array
     */
    private function getMethodArguments(File $phpcsFile, int $openParenthesisPtr, int $closedParenthesisPtr) : array
    {
        $tokens = $phpcsFile->getTokens();
        $methodArguments = [];
        for ($i = $openParenthesisPtr; $i < $closedParenthesisPtr; $i++) {
            $argumentsPtr = $phpcsFile->findNext(T_VARIABLE, $i + 1, $closedParenthesisPtr);
            if ($argumentsPtr === false) {
                break;
            } elseif ($argumentsPtr < $closedParenthesisPtr) {
                $arguments = $tokens[$argumentsPtr]['content'];
                $methodArguments[] = $arguments;
                $i = $argumentsPtr - 1;
            }
        }
        return $methodArguments;
    }

    /**
     * Get parameters from method annotation
     *
     * @param array $paramDefinitions
     * @return array
     */
    private function getMethodParameters(array $paramDefinitions) : array
    {
        $paramName = [];
        for ($i = 0; $i < count($paramDefinitions); $i++) {
            if (isset($paramDefinitions[$i]['paramName'])) {
                $paramName[] = $paramDefinitions[$i]['paramName'];
            }
        }
        return $paramName;
    }

    /**
     * Validates whether @inheritdoc without braces [@inheritdoc] exists or not
     *
     * @param File $phpcsFile
     * @param int $previousCommentOpenPtr
     * @param int $previousCommentClosePtr
     */
    private function validateInheritdocAnnotationWithoutBracesExists(
        File $phpcsFile,
        int $previousCommentOpenPtr,
        int $previousCommentClosePtr
    ) : bool {
        return $this->validateInheritdocAnnotationExists(
            $phpcsFile,
            $previousCommentOpenPtr,
            $previousCommentClosePtr,
            '@inheritdoc'
        );
    }

    /**
     * Validates whether @inheritdoc with braces [{@inheritdoc}] exists or not
     *
     * @param File $phpcsFile
     * @param int $previousCommentOpenPtr
     * @param int $previousCommentClosePtr
     */
    private function validateInheritdocAnnotationWithBracesExists(
        File $phpcsFile,
        int $previousCommentOpenPtr,
        int $previousCommentClosePtr
    ) : bool {
        return $this->validateInheritdocAnnotationExists(
            $phpcsFile,
            $previousCommentOpenPtr,
            $previousCommentClosePtr,
            '{@inheritdoc}'
        );
    }

    /**
     * Validates inheritdoc annotation exists
     *
     * @param File $phpcsFile
     * @param int $previousCommentOpenPtr
     * @param int $previousCommentClosePtr
     * @param string $inheritdocAnnotation
     * @return bool
     */
    private function validateInheritdocAnnotationExists(
        File $phpcsFile,
        int $previousCommentOpenPtr,
        int $previousCommentClosePtr,
        string $inheritdocAnnotation
    ) : bool {
        $tokens = $phpcsFile->getTokens();
        for ($ptr = $previousCommentOpenPtr; $ptr < $previousCommentClosePtr; $ptr++) {
            if (strtolower($tokens[$ptr]['content']) === $inheritdocAnnotation) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates if annotation exists for parameter in method annotation
     *
     * @param File $phpcsFile
     * @param int $argumentsCount
     * @param int $parametersCount
     * @param int $previousCommentOpenPtr
     * @param int $previousCommentClosePtr
     * @param int $stackPtr
     */
    private function validateParameterAnnotationForArgumentExists(
        File $phpcsFile,
        int $argumentsCount,
        int $parametersCount,
        int $previousCommentOpenPtr,
        int $previousCommentClosePtr,
        int $stackPtr
    ) : void {
        if ($argumentsCount > 0 && $parametersCount === 0) {
            $inheritdocAnnotationWithoutBracesExists = $this->validateInheritdocAnnotationWithoutBracesExists(
                $phpcsFile,
                $previousCommentOpenPtr,
                $previousCommentClosePtr
            );
            $inheritdocAnnotationWithBracesExists = $this->validateInheritdocAnnotationWithBracesExists(
                $phpcsFile,
                $previousCommentOpenPtr,
                $previousCommentClosePtr
            );
            if ($inheritdocAnnotationWithBracesExists) {
                $phpcsFile->addFixableError(
                    '{@inheritdoc} does not import parameter annotation',
                    $stackPtr,
                    'MethodArguments'
                );
            } elseif ($this->validateCommentBlockExists($phpcsFile, $previousCommentClosePtr, $stackPtr)
                && !$inheritdocAnnotationWithoutBracesExists
            ) {
                $phpcsFile->addFixableError(
                    'Missing @param for argument in method annotation',
                    $stackPtr,
                    'MethodArguments'
                );
            }
        }
    }

    /**
     * Validates whether comment block have extra the parameters listed in method annotation
     *
     * @param File $phpcsFile
     * @param int $argumentsCount
     * @param int $parametersCount
     * @param int $stackPtr
     */
    private function validateCommentBlockDoesnotHaveExtraParameterAnnotation(
        File $phpcsFile,
        int $argumentsCount,
        int $parametersCount,
        int $stackPtr
    ) : void {
        if ($argumentsCount < $parametersCount && $argumentsCount > 0) {
            $phpcsFile->addFixableError(
                'Extra @param found in method annotation',
                $stackPtr,
                'MethodArguments'
            );
        } elseif ($argumentsCount > 0 && $argumentsCount != $parametersCount && $parametersCount != 0) {
            $phpcsFile->addFixableError(
                '@param is not found for one or more params in method annotation',
                $stackPtr,
                'MethodArguments'
            );
        }
    }

    /**
     * Validates whether the argument name exists in method parameter annotation
     *
     * @param int $stackPtr
     * @param int $ptr
     * @param File $phpcsFile
     * @param array $methodArguments
     * @param array $paramDefinitions
     */
    private function validateArgumentNameInParameterAnnotationExists(
        int $stackPtr,
        int $ptr,
        File $phpcsFile,
        array $methodArguments,
        array $paramDefinitions
    ) : void {
        $parameterNames = $this->getMethodParameters($paramDefinitions);
        if (!in_array($methodArguments[$ptr], $parameterNames)) {
            $error = $methodArguments[$ptr]. ' parameter is missing in method annotation';
            $phpcsFile->addFixableError($error, $stackPtr, 'MethodArguments');
        }
    }

    /**
     * Validates whether parameter present in method signature
     *
     * @param int $ptr
     * @param int $paramDefinitionsArguments
     * @param array $methodArguments
     * @param File $phpcsFile
     * @param array $paramPointers
     */
    private function validateParameterPresentInMethodSignature(
        int $ptr,
        string $paramDefinitionsArguments,
        array $methodArguments,
        File $phpcsFile,
        array $paramPointers
    ) : void {
        if (!in_array($paramDefinitionsArguments, $methodArguments)) {
            $phpcsFile->addFixableError(
                $paramDefinitionsArguments . ' parameter is missing in method arguments signature',
                $paramPointers[$ptr],
                'MethodArguments'
            );
        }
    }

    /**
     * Validates whether the parameters are in order or not in method annotation
     *
     * @param array $paramDefinitions
     * @param array $methodArguments
     * @param File $phpcsFile
     * @param array $paramPointers
     */
    private function validateParameterOrderIsCorrect(
        array $paramDefinitions,
        array $methodArguments,
        File $phpcsFile,
        array $paramPointers
    ) : void {
        $parameterNames = $this->getMethodParameters($paramDefinitions);
        $paramDefinitionsCount = count($paramDefinitions);
        for ($ptr = 0; $ptr < $paramDefinitionsCount; $ptr++) {
            if (isset($methodArguments[$ptr]) && isset($parameterNames[$ptr])
                && in_array($methodArguments[$ptr], $parameterNames)
            ) {
                if ($methodArguments[$ptr] != $parameterNames[$ptr]) {
                    $phpcsFile->addFixableError(
                        $methodArguments[$ptr].' parameter is not in order',
                        $paramPointers[$ptr],
                        'MethodArguments'
                    );
                }
            }
        }
    }

    /**
     * Validate whether duplicate annotation present in method annotation
     *
     * @param int $stackPtr
     * @param array $paramDefinitions
     * @param array $paramPointers
     * @param File $phpcsFile
     * @param array $methodArguments
     */
    private function validateDuplicateAnnotationDoesnotExists(
        int $stackPtr,
        array $paramDefinitions,
        array $paramPointers,
        File $phpcsFile,
        array $methodArguments
    ) : void {
        $argumentsCount = count($methodArguments);
        $parametersCount = count($paramPointers);
        if ($argumentsCount <= $parametersCount && $argumentsCount > 0) {
            $duplicateParameters = [];
            for ($i = 0; $i < sizeof($paramDefinitions); $i++) {
                if (isset($paramDefinitions[$i]['paramName'])) {
                    $parameterContent = $paramDefinitions[$i]['paramName'];
                    for ($j = $i + 1; $j < count($paramDefinitions); $j++) {
                        if (isset($paramDefinitions[$j]['paramName'])
                            && $parameterContent === $paramDefinitions[$j]['paramName']
                        ) {
                            $duplicateParameters[] = $parameterContent;
                        }
                    }
                }
            }
            foreach ($duplicateParameters as $value) {
                $phpcsFile->addFixableError(
                    $value . ' duplicate found in method annotation',
                    $stackPtr,
                    'MethodArguments'
                );
            }
        }
    }

    /**
     * Validate parameter annotation format is correct or not
     *
     * @param int $ptr
     * @param File $phpcsFile
     * @param array $methodArguments
     * @param array $paramDefinitions
     * @param array $paramPointers
     */
    private function validateParameterAnnotationFormatIsCorrect(
        int $ptr,
        File $phpcsFile,
        array $methodArguments,
        array $paramDefinitions,
        array $paramPointers
    ) : void {
        switch (count($paramDefinitions)) {
            case 0:
                $phpcsFile->addFixableError(
                    'Missing both type and parameter',
                    $paramPointers[$ptr],
                    'MethodArguments'
                );
                break;
            case 1:
                if (preg_match('/^\$.*/', $paramDefinitions[0])) {
                    $phpcsFile->addError(
                        'Type is not specified',
                        $paramPointers[$ptr],
                        'MethodArguments'
                    );
                }
                break;
            case 2:
                if ($this->isInvalidType($paramDefinitions[0])) {
                    $phpcsFile->addFixableError(
                        $paramDefinitions[0].' is not a valid PHP type',
                        $paramPointers[$ptr],
                        'MethodArguments'
                    );
                }
                $this->validateParameterPresentInMethodSignature(
                    $ptr,
                    ltrim($paramDefinitions[1], '&'),
                    $methodArguments,
                    $phpcsFile,
                    $paramPointers
                );
                break;
            default:
                if (preg_match('/^\$.*/', $paramDefinitions[0])) {
                    $phpcsFile->addError(
                        'Type is not specified',
                        $paramPointers[$ptr],
                        'MethodArguments'
                    );
                    if ($this->isInvalidType($paramDefinitions[0])) {
                        $phpcsFile->addFixableError(
                            $paramDefinitions[0].' is not a valid PHP type',
                            $paramPointers[$ptr],
                            'MethodArguments'
                        );
                    }
                }
                break;
        }
    }

    /**
     * Validate method parameter annotations
     *
     * @param int $stackPtr
     * @param array $paramDefinitions
     * @param array $paramPointers
     * @param File $phpcsFile
     * @param array $methodArguments
     * @param int $previousCommentOpenPtr
     * @param int $previousCommentClosePtr
     */
    private function validateMethodParameterAnnotations(
        int $stackPtr,
        array $paramDefinitions,
        array $paramPointers,
        File $phpcsFile,
        array $methodArguments,
        int $previousCommentOpenPtr,
        int $previousCommentClosePtr
    ) : void {
        $argumentCount = count($methodArguments);
        $paramCount = count($paramPointers);
        $this->validateParameterAnnotationForArgumentExists(
            $phpcsFile,
            $argumentCount,
            $paramCount,
            $previousCommentOpenPtr,
            $previousCommentClosePtr,
            $stackPtr
        );
        $this->validateCommentBlockDoesnotHaveExtraParameterAnnotation(
            $phpcsFile,
            $argumentCount,
            $paramCount,
            $stackPtr
        );
        $this->validateDuplicateAnnotationDoesnotExists(
            $stackPtr,
            $paramDefinitions,
            $paramPointers,
            $phpcsFile,
            $methodArguments
        );
        $this->validateParameterOrderIsCorrect(
            $paramDefinitions,
            $methodArguments,
            $phpcsFile,
            $paramPointers
        );
        for ($ptr = 0; $ptr < count($methodArguments); $ptr++) {
            $tokens = $phpcsFile->getTokens();
            if (isset($paramPointers[$ptr])) {
                $this->validateArgumentNameInParameterAnnotationExists(
                    $stackPtr,
                    $ptr,
                    $phpcsFile,
                    $methodArguments,
                    $paramDefinitions
                );
                $paramContent = $tokens[$paramPointers[$ptr]+2]['content'];
                $paramContentExplode = explode(' ', $paramContent);
                $this->validateParameterAnnotationFormatIsCorrect(
                    $ptr,
                    $phpcsFile,
                    $methodArguments,
                    $paramContentExplode,
                    $paramPointers
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $numTokens = count($tokens);
        $previousCommentOpenPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr-1, 0);
        $previousCommentClosePtr = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, $stackPtr-1, 0);
        if (!$this->validateCommentBlockExists($phpcsFile, $previousCommentClosePtr, $stackPtr)) {
            $phpcsFile->addError('Comment block is missing', $stackPtr, 'MethodArguments');
            return;
        }
        $openParenthesisPtr = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr+1, $numTokens);
        $closedParenthesisPtr = $phpcsFile->findNext(T_CLOSE_PARENTHESIS, $stackPtr+1, $numTokens);
        $methodArguments = $this->getMethodArguments($phpcsFile, $openParenthesisPtr, $closedParenthesisPtr);
        $paramPointers = $paramDefinitions = [];
        for ($tempPtr = $previousCommentOpenPtr; $tempPtr < $previousCommentClosePtr; $tempPtr++) {
            if (strtolower($tokens[$tempPtr]['content']) === '@param') {
                $paramPointers[] = $tempPtr;
                $paramAnnotationParts = explode(' ', $tokens[$tempPtr+2]['content']);
                if (count($paramAnnotationParts) === 1) {
                    if ((preg_match('/^\$.*/', $paramAnnotationParts[0]))) {
                        $paramDefinitions[] = [
                            'type' => null,
                            'paramName' => rtrim(ltrim($tokens[$tempPtr+2]['content'], '&'), ',')
                        ];
                    } else {
                        $paramDefinitions[] = [
                            'type' => $tokens[$tempPtr+2]['content'],
                            'paramName' => null
                        ];
                    }
                } else {
                    $paramDefinitions[] = [
                        'type' => $paramAnnotationParts[0],
                        'paramName' => rtrim(ltrim($paramAnnotationParts[1], '&'), ',')
                    ];
                }
            }
        }
        $this->validateMethodParameterAnnotations(
            $stackPtr,
            $paramDefinitions,
            $paramPointers,
            $phpcsFile,
            $methodArguments,
            $previousCommentOpenPtr,
            $previousCommentClosePtr
        );
    }
}
