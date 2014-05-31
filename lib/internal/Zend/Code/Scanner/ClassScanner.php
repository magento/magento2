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

use Zend\Code\Exception;
use Zend\Code\NameInformation;

class ClassScanner implements ScannerInterface
{
    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @var string
     */
    protected $docComment = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $shortName = null;

    /**
     * @var int
     */
    protected $lineStart = null;

    /**
     * @var int
     */
    protected $lineEnd = null;

    /**
     * @var bool
     */
    protected $isFinal = false;

    /**
     * @var bool
     */
    protected $isAbstract = false;

    /**
     * @var bool
     */
    protected $isInterface = false;

    /**
     * @var string
     */
    protected $parentClass = null;

    /**
     * @var string
     */
    protected $shortParentClass = null;

    /**
     * @var string[]
     */
    protected $interfaces = array();

    /**
     * @var string[]
     */
    protected $shortInterfaces = array();

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * @var NameInformation
     */
    protected $nameInformation = null;

    /**
     * @var array[]
     */
    protected $infos = array();

    /**
     * @param array                $classTokens
     * @param NameInformation|null $nameInformation
     * @return ClassScanner
     */
    public function __construct(array $classTokens, NameInformation $nameInformation = null)
    {
        $this->tokens          = $classTokens;
        $this->nameInformation = $nameInformation;
    }

    public function getAnnotations()
    {
        return array();
    }

    public function getDocComment()
    {
        $this->scan();
        return $this->docComment;
    }

    public function getDocBlock()
    {
        if (!$docComment = $this->getDocComment()) {
            return false;
        }
        return new DocBlockScanner($docComment);
    }

    public function getName()
    {
        $this->scan();
        return $this->name;
    }

    public function getShortName()
    {
        $this->scan();
        return $this->shortName;
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

    public function isFinal()
    {
        $this->scan();
        return $this->isFinal;
    }

    public function isInstantiable()
    {
        $this->scan();
        return (!$this->isAbstract && !$this->isInterface);
    }

    public function isAbstract()
    {
        $this->scan();
        return $this->isAbstract;
    }

    public function isInterface()
    {
        $this->scan();
        return $this->isInterface;
    }

    public function hasParentClass()
    {
        $this->scan();
        return ($this->parentClass != null);
    }

    public function getParentClass()
    {
        $this->scan();
        return $this->parentClass;
    }

    public function getInterfaces()
    {
        $this->scan();
        return $this->interfaces;
    }

    public function getConstants()
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'constant') {
                continue;
            }
            $return[] = $info['name'];
        }
        return $return;
    }

    public function getPropertyNames()
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'property') {
                continue;
            }

            $return[] = $info['name'];
        }
        return $return;
    }

    public function getProperties()
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'property') {
                continue;
            }

            $return[] = $this->getProperty($info['name']);
        }
        return $return;
    }

    public function getMethodNames()
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'method') {
                continue;
            }

            $return[] = $info['name'];
        }

        return $return;
    }

    /**
     * @return MethodScanner[]
     */
    public function getMethods()
    {
        $this->scan();

        $return = array();

        foreach ($this->infos as $info) {
            if ($info['type'] != 'method') {
                continue;
            }

            $return[] = $this->getMethod($info['name']);
        }
        return $return;
    }

    /**
     * @param string|int $methodNameOrInfoIndex
     * @throws \Zend\Code\Exception\InvalidArgumentException
     * @return MethodScanner
     */
    public function getMethod($methodNameOrInfoIndex)
    {
        $this->scan();

        if (is_int($methodNameOrInfoIndex)) {
            $info = $this->infos[$methodNameOrInfoIndex];
            if ($info['type'] != 'method') {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a method');
            }
        } elseif (is_string($methodNameOrInfoIndex)) {
            $methodFound = false;
            foreach ($this->infos as $info) {
                if ($info['type'] === 'method' && $info['name'] === $methodNameOrInfoIndex) {
                    $methodFound = true;
                    break;
                }
            }
            if (!$methodFound) {
                return false;
            }
        }
        if (!isset($info)) {
            // @todo find a way to test this
            die('Massive Failure, test this');
        }
        $m = new MethodScanner(
            array_slice($this->tokens, $info['tokenStart'], $info['tokenEnd'] - $info['tokenStart'] + 1),
            $this->nameInformation
        );
        $m->setClass($this->name);
        $m->setScannerClass($this);
        return $m;
    }

    public function hasMethod($name)
    {
        $this->scan();

        foreach ($this->infos as $info) {
            if ($info['type'] === 'method' && $info['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    public static function export()
    {
        // @todo
    }

    public function __toString()
    {
        // @todo
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
        $namespace    = null;
        $infoIndex    = 0;
        $braceCount   = 0;

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
                $lastTokenArray = $token;
                list($tokenType, $tokenContent, $tokenLine) = $token;
            }
            return $tokenIndex;
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

        switch ($tokenType) {

            case T_DOC_COMMENT:

                $this->docComment = $tokenContent;
                goto SCANNER_CONTINUE;

            case T_FINAL:
            case T_ABSTRACT:
            case T_CLASS:
            case T_INTERFACE:

                // CLASS INFORMATION

                $classContext        = null;
                $classInterfaceIndex = 0;

                SCANNER_CLASS_INFO_TOP:

                if (is_string($tokens[$tokenIndex + 1]) && $tokens[$tokenIndex + 1] === '{') {
                    goto SCANNER_CLASS_INFO_END;
                }

                $this->lineStart = $tokenLine;

                switch ($tokenType) {

                    case T_FINAL:
                        $this->isFinal = true;
                        goto SCANNER_CLASS_INFO_CONTINUE;

                    case T_ABSTRACT:
                        $this->isAbstract = true;
                        goto SCANNER_CLASS_INFO_CONTINUE;

                    case T_INTERFACE:
                        $this->isInterface = true;
                    case T_CLASS:
                        $this->shortName = $tokens[$tokenIndex + 2][1];
                        if ($this->nameInformation && $this->nameInformation->hasNamespace()) {
                            $this->name = $this->nameInformation->getNamespace() . '\\' . $this->shortName;
                        } else {
                            $this->name = $this->shortName;
                        }
                        goto SCANNER_CLASS_INFO_CONTINUE;

                    case T_NS_SEPARATOR:
                    case T_STRING:
                        switch ($classContext) {
                            case T_EXTENDS:
                                $this->shortParentClass .= $tokenContent;
                                break;
                            case T_IMPLEMENTS:
                                $this->shortInterfaces[$classInterfaceIndex] .= $tokenContent;
                                break;
                        }
                        goto SCANNER_CLASS_INFO_CONTINUE;

                    case T_EXTENDS:
                    case T_IMPLEMENTS:
                        $classContext = $tokenType;
                        if (($this->isInterface && $classContext === T_EXTENDS) || $classContext === T_IMPLEMENTS) {
                            $this->shortInterfaces[$classInterfaceIndex] = '';
                        } elseif (!$this->isInterface && $classContext === T_EXTENDS) {
                            $this->shortParentClass = '';
                        }
                        goto SCANNER_CLASS_INFO_CONTINUE;

                    case null:
                        if ($classContext == T_IMPLEMENTS && $tokenContent == ',') {
                            $classInterfaceIndex++;
                            $this->shortInterfaces[$classInterfaceIndex] = '';
                        }

                }

                SCANNER_CLASS_INFO_CONTINUE:

                if ($MACRO_TOKEN_ADVANCE() === false) {
                    goto SCANNER_END;
                }
                goto SCANNER_CLASS_INFO_TOP;

                SCANNER_CLASS_INFO_END:

                goto SCANNER_CONTINUE;

        }

        if ($tokenType === null && $tokenContent === '{' && $braceCount === 0) {

            $braceCount++;
            if ($MACRO_TOKEN_ADVANCE() === false) {
                goto SCANNER_END;
            }

            SCANNER_CLASS_BODY_TOP:

            if ($braceCount === 0) {
                goto SCANNER_CLASS_BODY_END;
            }

            switch ($tokenType) {

                case T_CONST:

                    $infos[$infoIndex] = array(
                        'type'          => 'constant',
                        'tokenStart'    => $tokenIndex,
                        'tokenEnd'      => null,
                        'lineStart'     => $tokenLine,
                        'lineEnd'       => null,
                        'name'          => null,
                        'value'         => null,
                    );

                    SCANNER_CLASS_BODY_CONST_TOP:

                    if ($tokenContent === ';') {
                        goto SCANNER_CLASS_BODY_CONST_END;
                    }

                    if ($tokenType === T_STRING) {
                        $infos[$infoIndex]['name'] = $tokenContent;
                    }

                    SCANNER_CLASS_BODY_CONST_CONTINUE:

                    if ($MACRO_TOKEN_ADVANCE() === false) {
                        goto SCANNER_END;
                    }
                    goto SCANNER_CLASS_BODY_CONST_TOP;

                    SCANNER_CLASS_BODY_CONST_END:

                    $MACRO_INFO_ADVANCE();
                    goto SCANNER_CLASS_BODY_CONTINUE;

                case T_DOC_COMMENT:
                case T_PUBLIC:
                case T_PROTECTED:
                case T_PRIVATE:
                case T_ABSTRACT:
                case T_FINAL:
                case T_VAR:
                case T_FUNCTION:

                    $infos[$infoIndex] = array(
                        'type'        => null,
                        'tokenStart'  => $tokenIndex,
                        'tokenEnd'    => null,
                        'lineStart'   => $tokenLine,
                        'lineEnd'     => null,
                        'name'        => null,
                    );

                    $memberContext     = null;
                    $methodBodyStarted = false;

                    SCANNER_CLASS_BODY_MEMBER_TOP:

                    if ($memberContext === 'method') {
                        switch ($tokenContent) {
                            case '{':
                                $methodBodyStarted = true;
                                $braceCount++;
                                goto SCANNER_CLASS_BODY_MEMBER_CONTINUE;
                            case '}':
                                $braceCount--;
                                goto SCANNER_CLASS_BODY_MEMBER_CONTINUE;
                        }
                    }

                    if ($memberContext !== null) {
                        if (
                            ($memberContext === 'property' && $tokenContent === ';')
                            || ($memberContext === 'method' && $methodBodyStarted && $braceCount === 1)
                            || ($memberContext === 'method' && $this->isInterface && $tokenContent === ';')
                        ) {
                            goto SCANNER_CLASS_BODY_MEMBER_END;
                        }
                    }

                    switch ($tokenType) {

                        case T_VARIABLE:
                            if ($memberContext === null) {
                                $memberContext             = 'property';
                                $infos[$infoIndex]['type'] = 'property';
                                $infos[$infoIndex]['name'] = ltrim($tokenContent, '$');
                            }
                            goto SCANNER_CLASS_BODY_MEMBER_CONTINUE;

                        case T_FUNCTION:
                            $memberContext             = 'method';
                            $infos[$infoIndex]['type'] = 'method';
                            goto SCANNER_CLASS_BODY_MEMBER_CONTINUE;

                        case T_STRING:
                            if ($memberContext === 'method' && $infos[$infoIndex]['name'] === null) {
                                $infos[$infoIndex]['name'] = $tokenContent;
                            }
                            goto SCANNER_CLASS_BODY_MEMBER_CONTINUE;
                    }

                    SCANNER_CLASS_BODY_MEMBER_CONTINUE:

                    if ($MACRO_TOKEN_ADVANCE() === false) {
                        goto SCANNER_END;
                    }
                    goto SCANNER_CLASS_BODY_MEMBER_TOP;

                    SCANNER_CLASS_BODY_MEMBER_END:

                    $memberContext = null;
                    $MACRO_INFO_ADVANCE();
                    goto SCANNER_CLASS_BODY_CONTINUE;

                case null: // no type, is a string

                    switch ($tokenContent) {
                        case '{':
                            $braceCount++;
                            goto SCANNER_CLASS_BODY_CONTINUE;
                        case '}':
                            $braceCount--;
                            goto SCANNER_CLASS_BODY_CONTINUE;
                    }
            }

            SCANNER_CLASS_BODY_CONTINUE:

            if ($braceCount === 0 || $MACRO_TOKEN_ADVANCE() === false) {
                goto SCANNER_CONTINUE;
            }
            goto SCANNER_CLASS_BODY_TOP;

            SCANNER_CLASS_BODY_END:

            goto SCANNER_CONTINUE;

        }

        SCANNER_CONTINUE:

        if ($tokenContent === '}') {
            $this->lineEnd = $tokenLine;
        }

        if ($MACRO_TOKEN_ADVANCE() === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_END:

        // process short names
        if ($this->nameInformation) {
            if ($this->shortParentClass) {
                $this->parentClass = $this->nameInformation->resolveName($this->shortParentClass);
            }
            if ($this->shortInterfaces) {
                foreach ($this->shortInterfaces as $siIndex => $si) {
                    $this->interfaces[$siIndex] = $this->nameInformation->resolveName($si);
                }
            }
        } else {
            $this->parentClass = $this->shortParentClass;
            $this->interfaces  = $this->shortInterfaces;
        }

        $this->isScanned = true;
        return;
    }

}
