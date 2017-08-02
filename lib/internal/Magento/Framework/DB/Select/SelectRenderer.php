<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class SelectRenderer
 * @since 2.1.0
 */
class SelectRenderer implements RendererInterface
{
    /**
     * @var RendererInterface[]
     * @since 2.1.0
     */
    protected $renderers;

    /**
     * @param RendererInterface[] $renderers
     * @since 2.1.0
     */
    public function __construct(
        array $renderers
    ) {
        $this->renderers = $this->sort($renderers);
    }

    /**
     * Sort renderers
     *
     * @param array $renders
     * @return array
     * @since 2.1.0
     */
    protected function sort($renders)
    {
        $length = count($renders);
        if ($length <= 1) {
            return $renders;
        } else {
            $pivot = array_shift($renders);
            $left = $right = [];
            foreach ($renders as $render) {
                if ($render['sort'] < $pivot['sort']) {
                    $left[] = $render;
                } else {
                    $right[] = $render;
                }
            }

            return array_merge(
                $this->sort($left),
                [$pivot],
                $this->sort($right)
            );
        }
    }

    /**
     * Render SELECT statement
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        $sql = Select::SQL_SELECT;
        foreach ($this->renderers as $renderer) {
            if (in_array($renderer['part'], [Select::COLUMNS, Select::FROM]) || $select->getPart($renderer['part'])) {
                $sql = $renderer['renderer']->render($select, $sql);
            }
        }
        return $sql;
    }
}
