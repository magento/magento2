<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\Find;

/**
 * Class that holds the find structure are Clauses or Connectives for easy parsing
 */
class FindArgumentValue implements FindArgumentValueInterface
{
    /**
     * @var Clause[]|Connective[]
     */
    private $clauseList;

    /**
     * @param Clause[]|Connective[] $clauseList
     */
    public function __construct($clauseList)
    {
        $this->clauseList = $clauseList;
    }

    /**
     * {@inheritdoc}
     */
    public function getClauseList()
    {
        return $this->clauseList;
    }
}
