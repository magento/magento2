<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class FunctionCall extends AbstractExpression
{
    const FUNCTION_ABS = 'abs';
    const FUNCTION_ACOS = 'acos';
    const FUNCTION_ACOSH = 'acosh';
    const FUNCTION_ASIN = 'asin';
    const FUNCTION_ASINH = 'asinh';
    const FUNCTION_ATAN = 'atan';
    const FUNCTION_ATAN2 = 'atan2';
    const FUNCTION_ATANH = 'atanh';
    const FUNCTION_CEIL = 'ceil';
    const FUNCTION_COS = 'cos';
    const FUNCTION_COSH = 'cosh';
    const FUNCTION_EXP = 'exp';
    const FUNCTION_FLOOR = 'floor';
    const FUNCTION_HAVERSIN = 'haversin';
    const FUNCTION_LN = 'ln';
    const FUNCTION_LOG10 = 'log10';
    const FUNCTION_LOGN = 'logn';
    const FUNCTION_MAX = 'max';
    const FUNCTION_MIN = 'min';
    const FUNCTION_POW = 'pow';
    const FUNCTION_SIN = 'sin';
    const FUNCTION_SINH = 'sinh';
    const FUNCTION_SQRT = 'sqrt';
    const FUNCTION_TAN = 'tan';
    const FUNCTION_TANH = 'tanh';

    const FUNCTION_NAMES = [
        self::FUNCTION_ABS,
        self::FUNCTION_ACOS,
        self::FUNCTION_ACOSH,
        self::FUNCTION_ASIN,
        self::FUNCTION_ASINH,
        self::FUNCTION_ATAN,
        self::FUNCTION_ATAN2,
        self::FUNCTION_ATANH,
        self::FUNCTION_CEIL,
        self::FUNCTION_COS,
        self::FUNCTION_COSH,
        self::FUNCTION_EXP,
        self::FUNCTION_FLOOR,
        self::FUNCTION_HAVERSIN,
        self::FUNCTION_LN,
        self::FUNCTION_LOG10,
        self::FUNCTION_LOGN,
        self::FUNCTION_MAX,
        self::FUNCTION_MIN,
        self::FUNCTION_POW,
        self::FUNCTION_SIN,
        self::FUNCTION_SINH,
        self::FUNCTION_SQRT,
        self::FUNCTION_TAN,
        self::FUNCTION_TANH,
    ];

    /**
     * @var string
     */
    private $functionName;

    /**
     * @var ExpressionInterface[]
     */
    private $arguments;

    /**
     * @param string $functionName
     * @param ExpressionInterface ...$arguments
     * @throws \InvalidArgumentException
     */
    public function __construct(string $functionName, ExpressionInterface ...$arguments)
    {
        if (!in_array($functionName, static::FUNCTION_NAMES, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown function: "%s".', $functionName));
        }

        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return ExpressionInterface[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getSubExpressions(): array
    {
        return $this->getArguments();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFunctionName() . '(' . implode(',', array_map('strval', $this->getArguments())) . ')';
    }
}
