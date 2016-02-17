<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Console;

use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;

class InjectNamedConsoleParamsListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectNamedParams'), -80);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if a string is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function injectNamedParams(MvcEvent $e)
    {
        if (!$routeMatch = $e->getRouteMatch()) {
            return; // cannot work without route match
        }

        $request = $e->getRequest();
        if (!$request instanceof ConsoleRequest) {
            return; // will not inject non-console requests
        }

        // Inject route match params into request
        $params = array_merge(
            $request->getParams()->toArray(),
            $routeMatch->getParams()
        );
        $request->getParams()->fromArray($params);
    }
}
