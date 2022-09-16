<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
}
