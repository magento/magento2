<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Fixture\SalesRule;

use Magento\Mtf\Fixture\DataSource;

/**
 * Source for conditions serialized.
 */
class ConditionsSerialized extends DataSource
{
    /**
     * Path to chooser grid class
     *
     * @var array
     */
    protected $chooserGrid = [];

    /**
     * Path to additional chooser grid class
     *
     * @var array
     */
    protected $additionalChooserGrid = [];

    /**
     * @constructor
     * @param array $params
     * @param string $data
     */
    public function __construct(array $params, $data)
    {
        $this->chooserGrid = array_merge($this->chooserGrid, $this->additionalChooserGrid);
        $this->params = $params;
        foreach ($this->chooserGrid as $conditionsType => $chooserGrid) {
            $data = preg_replace(
                '#(' . preg_quote($conditionsType) . '\|.*?\|)([^\d].*?)#',
                '${1}%' . $chooserGrid['class'] . '#' . $chooserGrid['field'] . '%${2}',
                $data
            );
        }
        $this->data = $data;
    }
}
