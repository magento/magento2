<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene;

use Magento\Elasticsearch\Model\Lucene\Expression\ExpressionInterface;
use Magento\Elasticsearch\Model\Script\AbstractScript;

class Script extends AbstractScript
{
    /**
     * @var ExpressionInterface
     */
    private $rootExpression;

    /**
     * @param ExpressionInterface $rootExpression
     */
    public function __construct(ExpressionInterface $rootExpression)
    {
        $this->rootExpression = $rootExpression;
    }

    public function getLang(): string
    {
        return 'expression';
    }

    public function getReturnType(): string
    {
        return static::TYPE_DOUBLE;
    }

    public function getSource(): string
    {
        return (string) $this->rootExpression;
    }

    public function getParameterNames(): array
    {
        return $this->rootExpression->getParameterNames();
    }
}
