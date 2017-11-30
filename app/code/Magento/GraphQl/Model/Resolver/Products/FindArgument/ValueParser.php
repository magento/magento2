<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\Resolver\Products\FindArgument;

use Magento\Framework\GraphQl\Argument\Find\FindArgumentValueFactory;
use Magento\Framework\GraphQl\Argument\ValueParserInterface;

/**
 * Parses a mixed value to a FindArgumentValue
 */
class ValueParser implements ValueParserInterface
{
    /** @var ClauseConverter */
    private $clauseConverter;

    /** @var FindArgumentValueFactory */
    private $findArgumentValueFactory;

    /**
     * @param ClauseConverter $clauseConverter
     * @param FindArgumentValueFactory $findArgumentValueFactory
     */
    public function __construct(
        ClauseConverter $clauseConverter,
        FindArgumentValueFactory $findArgumentValueFactory
    ) {
        $this->clauseConverter = $clauseConverter;
        $this->findArgumentValueFactory = $findArgumentValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($value)
    {
        $filters = $this->clauseConverter->getFilterFromAst('product', $value);
        return $this->findArgumentValueFactory->create($filters);
    }
}
