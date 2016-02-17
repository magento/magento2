<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Stdlib/CallbackHandler.php';
#require_once 'Zend/Stdlib/SplPriorityQueue.php';

/**
 * Specialized priority queue implementation for use with an intercepting
 * filter chain.
 *
 * Allows removal
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_Filter_FilterIterator extends Zend_Stdlib_SplPriorityQueue
{
    /**
     * Does the queue contain a given value?
     *
     * @param  mixed $datum
     * @return bool
     */
    public function contains($datum)
    {
        $chain = clone $this;
        foreach ($chain as $item) {
            if ($item === $datum) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a value from the queue
     *
     * This is an expensive operation. It must first iterate through all values,
     * and then re-populate itself. Use only if absolutely necessary.
     *
     * @param  mixed $datum
     * @return bool
     */
    public function remove($datum)
    {
        $this->setExtractFlags(self::EXTR_BOTH);

        // Iterate and remove any matches
        $removed = false;
        $items   = array();
        $this->rewind();
        while (!$this->isEmpty()) {
            $item = $this->extract();
            if ($item['data'] === $datum) {
                $removed = true;
                continue;
            }
            $items[] = $item;
        }

        // Repopulate
        foreach ($items as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        $this->setExtractFlags(self::EXTR_DATA);
        return $removed;
    }

    /**
     * Iterate the next filter in the chain
     *
     * Iterates and calls the next filter in the chain.
     *
     * @param  mixed                                   $context
     * @param  array                                   $params
     * @param  Zend_EventManager_Filter_FilterIterator $chain
     * @return mixed
     */
    public function next($context = null, array $params = array(), $chain = null)
    {
        if (empty($context) || $chain->isEmpty()) {
            return;
        }

        $next = $this->extract();
        if (!$next instanceof Zend_Stdlib_CallbackHandler) {
            return;
        }

        $return = call_user_func($next->getCallback(), $context, $params, $chain);
        return $return;
    }
}
