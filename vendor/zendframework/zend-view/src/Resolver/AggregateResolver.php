<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Resolver;

use Countable;
use IteratorAggregate;
use Zend\Stdlib\PriorityQueue;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;

class AggregateResolver implements Countable, IteratorAggregate, ResolverInterface
{
    const FAILURE_NO_RESOLVERS = 'AggregateResolver_Failure_No_Resolvers';
    const FAILURE_NOT_FOUND    = 'AggregateResolver_Failure_Not_Found';

    /**
     * Last lookup failure
     * @var false|string
     */
    protected $lastLookupFailure = false;

    /**
     * @var Resolver
     */
    protected $lastSuccessfulResolver;

    /**
     * @var PriorityQueue
     */
    protected $queue;

    /**
     * Constructor
     *
     * Instantiate the internal priority queue
     *
     */
    public function __construct()
    {
        $this->queue = new PriorityQueue();
    }

    /**
     * Return count of attached resolvers
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * IteratorAggregate: return internal iterator
     *
     * @return PriorityQueue
     */
    public function getIterator()
    {
        return $this->queue;
    }

    /**
     * Attach a resolver
     *
     * @param  Resolver $resolver
     * @param  int $priority
     * @return AggregateResolver
     */
    public function attach(Resolver $resolver, $priority = 1)
    {
        $this->queue->insert($resolver, $priority);
        return $this;
    }

    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return false|string
     */
    public function resolve($name, Renderer $renderer = null)
    {
        $this->lastLookupFailure      = false;
        $this->lastSuccessfulResolver = null;

        if (0 === count($this->queue)) {
            $this->lastLookupFailure = static::FAILURE_NO_RESOLVERS;
            return false;
        }

        foreach ($this->queue as $resolver) {
            $resource = $resolver->resolve($name, $renderer);
            if ($resource) {
                // Resource found; return it
                $this->lastSuccessfulResolver = $resolver;
                return $resource;
            }
        }

        $this->lastLookupFailure = static::FAILURE_NOT_FOUND;
        return false;
    }

    /**
     * Return the last successful resolver, if any
     *
     * @return Resolver
     */
    public function getLastSuccessfulResolver()
    {
        return $this->lastSuccessfulResolver;
    }

    /**
     * Get last lookup failure
     *
     * @return false|string
     */
    public function getLastLookupFailure()
    {
        return $this->lastLookupFailure;
    }
}
