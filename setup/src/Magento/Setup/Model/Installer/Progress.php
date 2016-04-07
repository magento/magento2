<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Installer;

/**
 * Installation progress model
 */
class Progress
{
    /**
     * Total number of steps
     *
     * @var int
     */
    private $total;

    /**
     * Current step
     *
     * @var int
     */
    private $current;

    /**
     * Constructor
     *
     * @param int $total
     * @param int $current
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
     */
    public function finish()
    {
        $this->current = $this->total;
    }

    /**
     * Gets the current counter
     *
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Gets the total number
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Gets ratio of current to total
     *
     * @return float
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
