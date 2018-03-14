<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Argument\Filter;

/**
 * Class that holds the find structure is value of connective for easy parsing
 */
class FilterArgumentValue implements FilterArgumentValueInterface
{
    /**
     * @var Connective
     */
    private $value;

    /**
     * @param Connective $value
     */
    public function __construct(Connective $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue() : Connective
    {
        return $this->value;
    }
}
