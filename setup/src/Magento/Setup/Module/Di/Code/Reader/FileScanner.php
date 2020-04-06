<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * FileScanner code reader
 *
 * @SuppressWarnings(PHPMD)
 */
class FileScanner extends \Laminas\Code\Scanner\FileScanner
{
    /**
     * @var int
     */
    private $tokenType;

    /**
     * @inheritDoc
     */
    protected function scan()
    {
        if ($this->isScanned) {
            return;
        }

        if (!$this->tokens) {
            throw new \Laminas\Code\Exception\RuntimeException('No tokens were provided');
        }

        /**
         * Define PHP 5.4 'trait' token constant.
         */
        if (!defined('T_TRAIT')) {
            define('T_TRAIT', 42001);
        }

        /**
         * Variables & Setup
         */

        $tokens = &$this->tokens; // localize
        $infos = &$this->infos; // localize
        $tokenIndex = null;
        $token = null;
        $this->tokenType = null;
        $tokenContent = null;
        $tokenLine = null;
        $namespace = null;
        $docCommentIndex = false;
        $infoIndex = 0;

        /*
         * MACRO creation
         */
        $macroTokenAdvance = function () use (&$tokens, &$tokenIndex, &$token, &$tokenContent, &$tokenLine) {
            $tokenIndex = ($tokenIndex === null) ? 0 : $tokenIndex + 1;
            if (!isset($tokens[$tokenIndex])) {
                $token = false;
                $tokenContent = false;
                $this->tokenType = false;
                $tokenLine = false;

                return false;
            }
            if (is_string($tokens[$tokenIndex]) && $tokens[$tokenIndex] === '"') {
                do {
                    $tokenIndex++;
                } while (!(is_string($tokens[$tokenIndex]) && $tokens[$tokenIndex] === '"'));
            }
            $token = $tokens[$tokenIndex];
            if (is_array($token)) {
                list($this->tokenType, $tokenContent, $tokenLine) = $token;
            } else {
                $this->tokenType = null;
                $tokenContent = $token;
            }

            return $tokenIndex;
        };
        $macroTokenLogicalStartIndex = function () use (&$tokenIndex, &$docCommentIndex) {
            return ($docCommentIndex === false) ? $tokenIndex : $docCommentIndex;
        };
        $macroDocCommentStart = function () use (&$tokenIndex, &$docCommentIndex) {
            $docCommentIndex = $tokenIndex;

            return $docCommentIndex;
        };
        $macroDocCommentValidate = function () use (&$docCommentIndex) {
            static $validTrailingTokens = null;
            if ($validTrailingTokens === null) {
                $validTrailingTokens = [T_WHITESPACE, T_FINAL, T_ABSTRACT, T_INTERFACE, T_CLASS, T_FUNCTION];
            }
            if ($docCommentIndex !== false && !in_array($this->tokenType, $validTrailingTokens)) {
                $docCommentIndex = false;
            }

            return $docCommentIndex;
        };
        $macroInfoAdvance = function () use (&$infoIndex, &$infos, &$tokenIndex, &$tokenLine) {
            $infos[$infoIndex]['tokenEnd'] = $tokenIndex;
            $infos[$infoIndex]['lineEnd'] = $tokenLine;
            $infoIndex++;

            return $infoIndex;
        };

        // phpcs:disable
        /**
         * START FINITE STATE MACHINE FOR SCANNING TOKENS
         */

        // Initialize token
        $macroTokenAdvance();

        SCANNER_TOP:

        if ($token === false) {
            goto SCANNER_END;
        }

        // Validate current doc comment index
        $macroDocCommentValidate();

        switch ($this->tokenType) {
            case T_DOC_COMMENT:
                $macroDocCommentStart();
                goto SCANNER_CONTINUE;
            //goto no break needed

            case T_NAMESPACE:
                $infos[$infoIndex] = [
                    'type' => 'namespace',
                    'tokenStart' => $macroTokenLogicalStartIndex(),
                    'tokenEnd' => null,
                    'lineStart' => $token[2],
                    'lineEnd' => null,
                    'namespace' => null,
                ];

                // start processing with next token
                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }

                SCANNER_NAMESPACE_TOP:

                if ($this->tokenType === null && $tokenContent === ';' || $tokenContent === '{') {
                    goto SCANNER_NAMESPACE_END;
                }

                if ($this->tokenType === T_WHITESPACE) {
                    goto SCANNER_NAMESPACE_CONTINUE;
                }

                if ($this->tokenType === T_NS_SEPARATOR || $this->tokenType === T_STRING) {
                    $infos[$infoIndex]['namespace'] .= $tokenContent;
                }

                SCANNER_NAMESPACE_CONTINUE:

                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }
                goto SCANNER_NAMESPACE_TOP;

                SCANNER_NAMESPACE_END:

                $namespace = $infos[$infoIndex]['namespace'];

                $macroInfoAdvance();
                goto SCANNER_CONTINUE;
            //goto no break needed

            case T_USE:
                $infos[$infoIndex] = [
                    'type' => 'use',
                    'tokenStart' => $macroTokenLogicalStartIndex(),
                    'tokenEnd' => null,
                    'lineStart' => $tokens[$tokenIndex][2],
                    'lineEnd' => null,
                    'namespace' => $namespace,
                    'statements' => [0 => ['use' => null, 'as' => null]],
                ];

                $useStatementIndex = 0;
                $useAsContext = false;

                // start processing with next token
                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }

                SCANNER_USE_TOP:

                if ($this->tokenType === null) {
                    if ($tokenContent === ';') {
                        goto SCANNER_USE_END;
                    } elseif ($tokenContent === ',') {
                        $useAsContext = false;
                        $useStatementIndex++;
                        $infos[$infoIndex]['statements'][$useStatementIndex] = ['use' => null, 'as' => null];
                    }
                }

                // ANALYZE
                if ($this->tokenType !== null) {
                    if ($this->tokenType == T_AS) {
                        $useAsContext = true;
                        goto SCANNER_USE_CONTINUE;
                    }

                    if ($this->tokenType == T_NS_SEPARATOR || $this->tokenType == T_STRING) {
                        if ($useAsContext == false) {
                            $infos[$infoIndex]['statements'][$useStatementIndex]['use'] .= $tokenContent;
                        } else {
                            $infos[$infoIndex]['statements'][$useStatementIndex]['as'] = $tokenContent;
                        }
                    }
                }

                SCANNER_USE_CONTINUE:

                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }
                goto SCANNER_USE_TOP;

                SCANNER_USE_END:

                $macroInfoAdvance();
                goto SCANNER_CONTINUE;
            //goto no break needed

            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
                // Static for performance
                static $includeTypes = [
                    T_INCLUDE => 'include',
                    T_INCLUDE_ONCE => 'include_once',
                    T_REQUIRE => 'require',
                    T_REQUIRE_ONCE => 'require_once'
                ];

                $infos[$infoIndex] = [
                    'type' => 'include',
                    'tokenStart' => $macroTokenLogicalStartIndex(),
                    'tokenEnd' => null,
                    'lineStart' => $tokens[$tokenIndex][2],
                    'lineEnd' => null,
                    'includeType' => $includeTypes[$tokens[$tokenIndex][0]],
                    'path' => '',
                ];

                // start processing with next token
                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }

                SCANNER_INCLUDE_TOP:

                if ($this->tokenType === null && $tokenContent === ';') {
                    goto SCANNER_INCLUDE_END;
                }

                $infos[$infoIndex]['path'] .= $tokenContent;

                SCANNER_INCLUDE_CONTINUE:

                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }
                goto SCANNER_INCLUDE_TOP;

                SCANNER_INCLUDE_END:

                $macroInfoAdvance();
                goto SCANNER_CONTINUE;
            //goto no break needed

            case T_FUNCTION:
            case T_FINAL:
            case T_ABSTRACT:
            case T_CLASS:
            case T_INTERFACE:
            case T_TRAIT:
                $infos[$infoIndex] = [
                    'type' => ($this->tokenType === T_FUNCTION) ? 'function' : 'class',
                    'tokenStart' => $macroTokenLogicalStartIndex(),
                    'tokenEnd' => null,
                    'lineStart' => $tokens[$tokenIndex][2],
                    'lineEnd' => null,
                    'namespace' => $namespace,
                    'uses' => $this->getUsesNoScan($namespace),
                    'name' => null,
                    'shortName' => null,
                ];

                $classBraceCount = 0;

                // start processing with current token

                SCANNER_CLASS_TOP:

                // process the name
                if ($infos[$infoIndex]['shortName'] == ''
                    && (($this->tokenType === T_CLASS
                            || $this->tokenType === T_INTERFACE
                            || $this->tokenType === T_TRAIT
                        )
                        && $infos[$infoIndex]['type'] === 'class' && $tokens[$tokenIndex - 1][0] !== T_DOUBLE_COLON
                        || ($this->tokenType === T_FUNCTION && $infos[$infoIndex]['type'] === 'function'))
                ) {
                    $infos[$infoIndex]['shortName'] = $tokens[$tokenIndex + 2][1];
                    $infos[$infoIndex]['name'] = (($namespace !== null)
                            ? $namespace . '\\'
                            : '') . $infos[$infoIndex]['shortName'];
                }

                if ($this->tokenType === null) {
                    if ($tokenContent == '{') {
                        $classBraceCount++;
                    }
                    if ($tokenContent == '}') {
                        $classBraceCount--;
                        if ($classBraceCount === 0) {
                            goto SCANNER_CLASS_END;
                        }
                    }
                }

                SCANNER_CLASS_CONTINUE:

                if ($macroTokenAdvance() === false) {
                    goto SCANNER_END;
                }
                goto SCANNER_CLASS_TOP;

                SCANNER_CLASS_END:

                $macroInfoAdvance();
                goto SCANNER_CONTINUE;
        }

        SCANNER_CONTINUE:

        if ($macroTokenAdvance() === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_END:

        /**
         * END FINITE STATE MACHINE FOR SCANNING TOKENS
         */
        $this->isScanned = true;
        // phpcs:enable
    }
}
