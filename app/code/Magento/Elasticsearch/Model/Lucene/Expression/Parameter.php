<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class Parameter extends AbstractExpression
{
    /**
     * @var string
     */
    private $parameterName;

    /**
     * @param string $parameterName
     */
    public function __construct(string $parameterName)
    {
        if (!preg_match('/^\w+$/', $parameterName)) {
            throw new \InvalidArgumentException(sprintf('Invalid parameter name: "%s".', $parameterName));
        }

        $this->parameterName = $parameterName;
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getParameterNames(): array
    {
        return [ $this->getParameterName() ];
    }

    public function __toString(): string
    {
        return $this->parameterName;
    }
}
