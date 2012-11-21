<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Scanner;

use Zend\Code\Annotation;
use Zend\Code\Exception;
use Zend\Code\NameInformation;

class MethodScanner implements ScannerInterface
{
    protected $isScanned    = false;

    protected $docComment   = null;
    protected $scannerClass = null;
    protected $class        = null;
    protected $name         = null;
    protected $lineStart    = null;
    protected $lineEnd      = null;
    protected $isFinal      = false;
    protected $isAbstract   = false;
    protected $isPublic     = true;
    protected $isProtected  = false;
    protected $isPrivate    = false;
    protected $isStatic     = false;

    protected $body         = '';

    protected $tokens       = array();
    protected $nameInformation = null;
    protected $infos        = array();

    public function __construct(array $methodTokens, NameInformation $nameInformation = null)
    {
        $this->tokens          = $methodTokens;
        $this->nameInformation = $nameInformation;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setScannerClass(ClassScanner $scannerClass)
    {
        $this->scannerClass = $scannerClass;
    }

    public function getClassScanner()
    {
        return $this->scannerClass;
    }

    public function getName()
    {
        $this->scan();
        return $this->name;
    }

    public function getLineStart()
    {
        $this->scan();
        return $this->lineStart;
    }

    public function getLineEnd()
    {
        $this->scan();
        return $this->lineEnd;
    }

    public function getDocComment()
    {
        $this->scan();
        return $this->docComment;
    }

    /**
     * @param  Annotation\AnnotationManager $annotationManager
     * @return Annotation\AnnotationCollection
     */
    public function getAnnotations(Annotation\AnnotationManager $annotationManager)
    {
        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        return new AnnotationScanner($annotationManager, $docComment, $this->nameInformation);
    }

    public function isFinal()
    {
        $this->scan();
        return $this->isFinal;
    }

    public function isAbstract()
    {
        $this->scan();
        return $this->isAbstract;
    }

    public function isPublic()
    {
        $this->scan();
        return $this->isPublic;
    }

    public function isProtected()
    {
        $this->scan();
        return $this->isProtected;
    }

    public function isPrivate()
    {
        $this->scan();
        return $this->isPrivate;
    }

    public function isStatic()
    {
        $this->scan();
        return $this->isStatic;
    }

    public function getNumberOfParameters()
    {
        return count($this->getParameters());
    }

    public function getParameters($returnScanner = false)
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'parameter') {
                continue;
            }

            if (!$returnScanner) {
                $return[] = $info['name'];
            } else {
                $return[] = $this->getParameter($info['name']);
            }
        }
        return $return;
    }

    public function getParameter($parameterNameOrInfoIndex)
    {
        $this->scan();

        if (is_int($parameterNameOrInfoIndex)) {
            $info = $this->infos[$parameterNameOrInfoIndex];
            if ($info['type'] != 'parameter') {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a parameter');
            }
        } elseif (is_string($parameterNameOrInfoIndex)) {
            foreach ($this->infos as $info) {
                if ($info['type'] === 'parameter' && $info['name'] === $parameterNameOrInfoIndex) {
                    break;
                }
                unset($info);
            }
            if (!isset($info)) {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a parameter');
            }
        }

        $p = new ParameterScanner(
            array_slice($this->tokens, $info['tokenStart'], $info['tokenEnd'] - $info['tokenStart']),
            $this->nameInformation
        );
        $p->setDeclaringFunction($this->name);
        $p->setDeclaringScannerFunction($this);
        $p->setDeclaringClass($this->class);
        $p->setDeclaringScannerClass($this->scannerClass);
        $p->setPosition($info['position']);
        return $p;
    }

    public function getBody()
    {
        $this->scan();
        return $this->body;
    }

    public static function export()
    {
        // @todo
    }

    public function __toString()
    {
        $this->scan();
        return var_export($this, true);
    }

    protected function scan()
    {
        if ($this->isScanned) {
            return;
        }

        if (!$this->tokens) {
            throw new Exception\RuntimeException('No tokens were provided');
        }

        /**
         * Variables & Setup
         */

        $tokens       = &$this->tokens; // localize
        $infos        = &$this->infos; // localize
        $tokenIndex   = null;
        $token        = null;
        $tokenType    = null;
        $tokenContent = null;
        $tokenLine    = null;
        $infoIndex    = 0;
        $parentCount  = 0;

        /*
         * MACRO creation
         */
        $MACRO_TOKEN_ADVANCE = function() use (&$tokens, &$tokenIndex, &$token, &$tokenType, &$tokenContent, &$tokenLine) {
            static $lastTokenArray = null;
            $tokenIndex = ($tokenIndex === null) ? 0 : $tokenIndex + 1;
            if (!isset($tokens[$tokenIndex])) {
                $token        = false;
                $tokenContent = false;
                $tokenType    = false;
                $tokenLine    = false;
                return false;
            }
            $token = $tokens[$tokenIndex];
            if (is_string($token)) {
                $tokenType    = null;
                $tokenContent = $token;
                $tokenLine    = $tokenLine + substr_count($lastTokenArray[1],
                                                          "\n"); // adjust token line by last known newline count
            } else {
                list($tokenType, $tokenContent, $tokenLine) = $token;
            }
            return $tokenIndex;
        };
        $MACRO_INFO_START    = function() use (&$infoIndex, &$infos, &$tokenIndex, &$tokenLine) {
            $infos[$infoIndex] = array(
                'type'        => 'parameter',
                'tokenStart'  => $tokenIndex,
                'tokenEnd'    => null,
                'lineStart'   => $tokenLine,
                'lineEnd'     => $tokenLine,
                'name'        => null,
                'position'    => $infoIndex + 1, // position is +1 of infoIndex
            );
        };
        $MACRO_INFO_ADVANCE  = function() use (&$infoIndex, &$infos, &$tokenIndex, &$tokenLine) {
            $infos[$infoIndex]['tokenEnd'] = $tokenIndex;
            $infos[$infoIndex]['lineEnd']  = $tokenLine;
            $infoIndex++;
            return $infoIndex;
        };

        /**
         * START FINITE STATE MACHINE FOR SCANNING TOKENS
         */

        // Initialize token
        $MACRO_TOKEN_ADVANCE();

        SCANNER_TOP:


        $this->lineStart = ($this->lineStart) ? : $tokenLine;

        switch ($tokenType) {
            case T_DOC_COMMENT:
                $this->lineStart = null;
                if ($this->docComment === null && $this->name === null) {
                    $this->docComment = $tokenContent;
                }
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_FINAL:
                $this->isFinal = true;
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_ABSTRACT:
                $this->isAbstract = true;
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_PUBLIC:
                // use defaults
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_PROTECTED:
                $this->isProtected = true;
                $this->isPublic    = false;
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_PRIVATE:
                $this->isPrivate = true;
                $this->isPublic  = false;
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_STATIC:
                $this->isStatic = true;
                goto SCANNER_CONTINUE_SIGNATURE;

            case T_VARIABLE:
            case T_STRING:

                if ($tokenType === T_STRING && $parentCount === 0) {
                    $this->name = $tokenContent;
                }

                if ($parentCount === 1) {
                    if (!isset($infos[$infoIndex])) {
                        $MACRO_INFO_START();
                    }
                    if ($tokenType === T_VARIABLE) {
                        $infos[$infoIndex]['name'] = ltrim($tokenContent, '$');
                    }
                }

                goto SCANNER_CONTINUE_SIGNATURE;

            case null:

                switch ($tokenContent) {
                    case '&':
                        if (!isset($infos[$infoIndex])) {
                            $MACRO_INFO_START();
                        }
                        goto SCANNER_CONTINUE_SIGNATURE;
                    case '(':
                        $parentCount++;
                        goto SCANNER_CONTINUE_SIGNATURE;
                    case ')':
                        $parentCount--;
                        if ($parentCount === 0) {
                            if ($infos) {
                                $MACRO_INFO_ADVANCE();
                            }
                            $context = 'body';
                        }
                        goto SCANNER_CONTINUE_BODY;
                    case ',':
                        if ($parentCount === 1) {
                            $MACRO_INFO_ADVANCE();
                        }
                        goto SCANNER_CONTINUE_SIGNATURE;
                }
        }

        SCANNER_CONTINUE_SIGNATURE:

        if ($MACRO_TOKEN_ADVANCE() === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_CONTINUE_BODY:

        $braceCount = 0;
        while ($MACRO_TOKEN_ADVANCE() !== false) {
            if ($tokenContent == '}') {
                $braceCount--;
            }
            if ($braceCount > 0) {
                $this->body .= $tokenContent;
            }
            if ($tokenContent == '{') {
                $braceCount++;
            }
            $this->lineEnd = $tokenLine;
        }

        SCANNER_END:

        $this->isScanned = true;
        return;
    }

}
