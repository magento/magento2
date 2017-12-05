<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\Find;

/**
 * Class that holds the find structure are value or connective for easy parsing
 */
class FindArgumentValue implements FindArgumentValueInterface
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
