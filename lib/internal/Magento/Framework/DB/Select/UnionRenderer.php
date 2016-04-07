<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class UnionRenderer
 */
class UnionRenderer implements RendererInterface
{
    /**
     * Render UNION section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::UNION)) {
            $sql = '';
            $parts = count($select->getPart(Select::UNION));
            foreach ($select->getPart(Select::UNION) as $cnt => $union) {
                list($target, $type) = $union;
                if ($target instanceof Select) {
                    $target = $target->assemble();
                }
                $sql .= $target;
                if ($cnt < $parts - 1) {
                    $sql .= ' ' . $type . ' ';
                }
            }
        }
        return $sql;
    }
}
