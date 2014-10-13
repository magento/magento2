<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
