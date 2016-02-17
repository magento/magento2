<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager\Filter;

use Zend\EventManager\ResponseCollection;
use Zend\Stdlib\CallbackHandler;

/**
 * Interface for intercepting filter chains
 */
interface FilterInterface
{
    /**
     * Execute the filter chain
     *
     * @param  string|object $context
     * @param  array $params
     * @return mixed
     */
    public function run($context, array $params = array());

    /**
     * Attach an intercepting filter
     *
     * @param  callable $callback
     * @return CallbackHandler
     */
    public function attach($callback);

    /**
     * Detach an intercepting filter
     *
     * @param  CallbackHandler $filter
     * @return bool
     */
    public function detach(CallbackHandler $filter);

    /**
     * Get all intercepting filters
     *
     * @return array
     */
    public function getFilters();

    /**
     * Clear all filters
     *
     * @return void
     */
    public function clearFilters();

    /**
     * Get all filter responses
     *
     * @return ResponseCollection
     */
    public function getResponses();
}
