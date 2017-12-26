<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    public function getValue()
    {
        return $this->value;
    }
}
