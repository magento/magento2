<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\Find;

/**
 * Class that holds the find structure are Clause or Connective for easy parsing
 */
class FindArgumentValue implements FindArgumentValueInterface
{
    /**
     * @var Clause|Connective
     */
    private $clause;

    /**
     * @param Clause|Connective $clause
     */
    public function __construct($clause)
    {
        $this->clause = $clause;
    }

    /**
     * {@inheritdoc}
     */
    public function getClause()
    {
        return $this->clause;
    }
}
