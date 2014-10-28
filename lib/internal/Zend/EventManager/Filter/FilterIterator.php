<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_EventManager
 */

namespace Zend\EventManager\Filter;

use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\SplPriorityQueue;

/**
 * Specialized priority queue implementation for use with an intercepting
 * filter chain.
 *
 * Allows removal
 *
 * @category   Zend
 * @package    Zend_EventManager
 */
class FilterIterator extends SplPriorityQueue
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
     * @param  mixed $context
     * @param  array $params
     * @param  FilterIterator $chain
     * @return mixed
     */
    public function next($context = null, array $params = array(), $chain = null)
    {
        if (empty($context) || $chain->isEmpty()) {
            return;
        }

        $next = $this->extract();
        if (!$next instanceof CallbackHandler) {
            return;
        }

        $return = call_user_func($next->getCallback(), $context, $params, $chain);
        return $return;
    }
}
