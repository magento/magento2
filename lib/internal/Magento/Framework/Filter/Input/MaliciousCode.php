<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Input;

use Laminas\Filter\FilterInterface;
use Magento\Framework\App\ObjectManager;

class MaliciousCode implements FilterInterface
{
    /**
     * @var PurifierInterface|null $purifier
     */
    private PurifierInterface $purifier;

    /**
     * @param PurifierInterface|null $purifier
     */
    public function __construct(?PurifierInterface $purifier = null)
    {
        $this->purifier =  $purifier ?? ObjectManager::getInstance()->get(PurifierInterface::class);
    }

    /**
     * Regular expressions for cutting malicious code
     *
     * @var string[]
     */
    protected array $_expressions = [
        //comments, must be first
        '/(\/\*.*\*\/)/Us',
        //tabs
        '/(\t)/',
        //javasript prefix
        '/(javascript\s*:)/Usi',
        //import styles
        '/(@import)/Usi',
        //js in the style attribute
        '/style=[^<]*((expression\s*?\([^<]*?\))|(behavior\s*:))[^<]*(?=\/*\>)/Uis',
        //js attributes
        '/(ondblclick|onclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|' .
        'onload|onunload|onerror)=[^<]*(?=\/*\>)/Uis',
        //tags
        '/<\/?(script|meta|link|frame|iframe|object).*>/Uis',
        //scripts
        '/<\?\s*?(php|=).*>/Uis',
        //base64 usage
        '/src=[^<]*base64[^<]*(?=\/*\>)/Uis',
    ];

    /**
     * Regular expressions for path validation
     *
     * @var string
     */
    private const PATH_EXPRESSION =
        '/(?:^|[\/\\\\])(' .
        '(?:' .
        '(?:\.\.|%2e%2e|%252e%252e|%c0%2e%2e|%c0%ae%c0%ae|%e0%40%ae%2e|%c0%2e%c0%2e)' .
        '(?:[\/\\\\]|%2f|%5c|%255c)' .
        ')' .
        ')|\\x00|%00/i';

    /**
     * Filter value
     *
     * @param string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        $replaced = 0;
        do {
            $value = preg_replace($this->_expressions, '', $value ?? '', -1, $replaced);
        } while ($replaced !== 0);

        return $this->purifier->purify($value);
    }

    /**
     * Add expression
     *
     * @param string $expression
     * @return $this
     */
    public function addExpression(string $expression) :self
    {
        if (!in_array($expression, $this->_expressions)) {
            $this->_expressions[] = $expression;
        }
        return $this;
    }

    /**
     * Set expressions
     *
     * @param array $expressions
     * @return $this
     */
    public function setExpressions(array $expressions) :self
    {
        $this->_expressions = $expressions;
        return $this;
    }

    /**
     * Check if the path is valid
     *
     * @param string $fileName
     * @return bool
     */
    public function isValidPath(string $fileName): bool
    {
        return !preg_match(self::PATH_EXPRESSION, $fileName);
    }
}
