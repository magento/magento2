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

#require_once 'Zend/EventManager/Filter.php';
#require_once 'Zend/EventManager/Filter/FilterIterator.php';
#require_once 'Zend/Stdlib/CallbackHandler.php';

/**
 * FilterChain: intercepting filter manager
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_FilterChain implements Zend_EventManager_Filter
{
    /**
     * @var Zend_EventManager_Filter_FilterIterator All filters
     */
    protected $filters;

    /**
     * Constructor
     *
     * Initializes Zend_EventManager_Filter_FilterIterator in which filters will be aggregated
     *
     * @return void
     */
    public function __construct()
    {
        $this->filters = new Zend_EventManager_Filter_FilterIterator();
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
        if (!$next instanceof Zend_Stdlib_CallbackHandler) {
            return;
        }

        return call_user_func($next->getCallback(), $context, $argv, $chain);
    }

    /**
     * Connect a filter to the chain
     *
     * @param  callback $callback PHP Callback
     * @param  int      $priority Priority in the queue at which to execute; defaults to 1 (higher numbers == higher priority)
     * @throws Zend_Stdlib_Exception_InvalidCallbackException
     * @return Zend_Stdlib_CallbackHandler (to allow later unsubscribe)
     */
    public function attach($callback, $priority = 1)
    {
        if (empty($callback)) {
            #require_once 'Zend/Stdlib/Exception/InvalidCallbackException.php';
            throw new Zend_Stdlib_Exception_InvalidCallbackException('No callback provided');
        }
        $filter = new Zend_Stdlib_CallbackHandler($callback, array('priority' => $priority));
        $this->filters->insert($filter, $priority);
        return $filter;
    }

    /**
     * Detach a filter from the chain
     *
     * @param  Zend_Stdlib_CallbackHandler $filter
     * @return bool Returns true if filter found and unsubscribed; returns false otherwise
     */
    public function detach(Zend_Stdlib_CallbackHandler $filter)
    {
        return $this->filters->remove($filter);
    }

    /**
     * Retrieve all filters
     *
     * @return Zend_EventManager_Filter_FilterIterator
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
        $this->filters = new Zend_EventManager_Filter_FilterIterator();
    }

    /**
     * Return current responses
     *
     * Only available while the chain is still being iterated. Returns the
     * current ResponseCollection.
     *
     * @return null|Zend_EventManager_ResponseCollection
     */
    public function getResponses()
    {
        return $this->responses;
    }
}
