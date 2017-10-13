<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Schedule cron expression parser
 *
 * @api
 */
class Parser implements ParserInterface
{
    /**
     * @var PartFactory
     */
    private $partFactory;

    /**
     * @param PartFactory $partFactory
     */
    public function __construct(
        PartFactory $partFactory
    ) {
        $this->partFactory = $partFactory;
    }

    /**
     * Perform parsing of cron expression
     *
     * @param ExpressionInterface $expression
     *
     * @return bool|PartInterface[]
     */
    public function parse(ExpressionInterface $expression)
    {
        if (!strlen($expression->getCronExpr())) {
            return false;
        }

        $stringParts = preg_split('#\s+#', $expression->getCronExpr(), null, PREG_SPLIT_NO_EMPTY);

        $parts = [];
        foreach ($stringParts as $partIndex => $partValue) {
            $parts[] = $this->partFactory->create($partIndex, $partValue);
        }

        return $parts;
    }
}
