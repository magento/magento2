<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Installer;

/**
 * Installation progress model
 * @since 2.0.0
 */
class Progress
{
    /**
     * Total number of steps
     *
     * @var int
     * @since 2.0.0
     */
    private $total;

    /**
     * Current step
     *
     * @var int
     * @since 2.0.0
     */
    private $current;

    /**
     * Constructor
     *
     * @param int $total
     * @param int $current
     * @since 2.0.0
     */
    public function __construct($total, $current = 0)
    {
        $this->validate($total, $current);
        $this->total = $total;
        $this->current = $current;
    }

    /**
     * Increments current counter
     *
     * @return void
     * @since 2.0.0
     */
    public function setNext()
    {
        $this->validate($this->total, $this->current + 1);
        $this->current++;
    }

    /**
     * Sets current counter to the end
     *
     * @return void
     * @since 2.0.0
     */
    public function finish()
    {
        $this->current = $this->total;
    }

    /**
     * Gets the current counter
     *
     * @return int
     * @since 2.0.0
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Gets the total number
     *
     * @return int
     * @since 2.0.0
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Gets ratio of current to total
     *
     * @return float
     * @since 2.0.0
     */
    public function getRatio()
    {
        return $this->current / $this->total;
    }

    /**
     * Asserts invariants
     *
     * @param int $total
     * @param int $current
     * @return void
     * @throws \LogicException
     * @since 2.0.0
     */
    private function validate($total, $current)
    {
        if (empty($total) || 0 >= $total) {
            throw new \LogicException('Total number must be more than zero.');
        }
        if ($current > $total) {
            throw new \LogicException('Current cannot exceed total number.');
        }
    }
}
