<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Grid\Sql;

/**
 * Class Concat
 */
class Concat
{
    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @param string[] $columns
     * @param string $separator
     */
    public function __construct(array $columns, $separator = ' ')
    {
        $this->columns = $columns;
        $this->separator = $separator;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $columns = [];
        foreach ($this->columns as $key => $column) {
            $columns[$key] = sprintf("ifnull(%s, '')", $column);
        }
        return sprintf(
            'trim(concat(%s))',
            implode(
                sprintf(", '%s' ,", $this->separator),
                $columns
            )
        );
    }
}
