<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_EventManager
 */

namespace Zend\EventManager;

use Zend\Stdlib\CallbackHandler;

/**
 * FilterChain: intercepting filter manager
 *
 * @category   Zend
 * @package    Zend_EventManager
 */
class FilterChain implements Filter\FilterInterface
{
    /**
     * @var Filter\FilterIterator All filters
     */
    protected $filters;

    /**
     * Constructor
     *
     * Initializes Filter\FilterIterator in which filters will be aggregated
     */
    public function __construct()
    {
        $this->filters = new Filter\FilterIterator();
    }

    /**
     * Apply the filters
     *
     * Begins iteration of the filters.
     *
     * @param  mixed $context Object under observation
     * @param  mixed $argv Associative array of arguments
     * @return mixed
     */
    public function run($context, array $argv = array())
    {
        $chain = clone $this->getFilters();

        if ($chain->isEmpty()) {
            return;
        }

        $next = $chain->extract();
        if (!$next instanceof CallbackHandler) {
            return;
        }

        return call_user_func($next->getCallback(), $context, $argv, $chain);
    }

    /**
     * Connect a filter to the chain
     *
     * @param  callable $callback PHP Callback
     * @param  int $priority Priority in the queue at which to execute; defaults to 1 (higher numbers == higher priority)
     * @return CallbackHandler (to allow later unsubscribe)
     * @throws Exception\InvalidCallbackException
     */
    public function attach($callback, $priority = 1)
    {
        if (empty($callback)) {
            throw new Exception\InvalidCallbackException('No callback provided');
        }
        $filter = new CallbackHandler($callback, array('priority' => $priority));
        $this->filters->insert($filter, $priority);
        return $filter;
    }

    /**
     * Detach a filter from the chain
     *
     * @param  CallbackHandler $filter
     * @return bool Returns true if filter found and unsubscribed; returns false otherwise
     */
    public function detach(CallbackHandler $filter)
    {
        return $this->filters->remove($filter);
    }

    /**
     * Retrieve all filters
     *
     * @return Filter\FilterIterator
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Clear all filters
     *
     * @return void
     */
    public function clearFilters()
    {
        $this->filters = new Filter\FilterIterator();
    }

    /**
     * Return current responses
     *
     * Only available while the chain is still being iterated. Returns the
     * current ResponseCollection.
     *
     * @return null|ResponseCollection
     */
    public function getResponses()
    {
        return null;
    }
}
