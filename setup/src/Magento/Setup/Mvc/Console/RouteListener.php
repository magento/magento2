<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Mvc\Console;

use Zend\View\Model\ConsoleModel;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\MvcEvent;
use Zend\Console\ColorInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Custom route listener to validate user parameters
 */
class RouteListener extends \Zend\Mvc\RouteListener
{
    /**
     * {@inheritdoc}
     */
    public function onRoute($e)
    {
        $request = $e->getRequest();
        // propagates to default RouteListener if not CLI
        if (!$request instanceof \Zend\Console\Request) {
            return null;
        }

        $router = $e->getRouter();
        $match = $router->match($request);

        // CLI routing miss, checks for missing/extra parameters
        if (!$match instanceof RouteMatch) {
            $content = $request->getContent();
            $config = $e->getApplication()->getServiceManager()->get('Config')['console']['router']['routes'];

            $verboseValidator = new VerboseValidator();
            $validationMessages = $verboseValidator->validate($content, $config);

            if ('' !== $validationMessages) {
                $this->displayMessages($e, $validationMessages);
                // set error to stop propagation
                $e->setError('default_error');
            }
        }
        return null;
    }

    /**
     * Display messages on console
     *
     * @param MvcEvent $e
     * @param string $validationMessages
     * @return void
     */
    private function displayMessages(MvcEvent $e, $validationMessages)
    {
        /** @var \Zend\Console\Adapter\AdapterInterface $console */
        $console = $e->getApplication()->getServiceManager()->get('console');
        $validationMessages = $console->colorize($validationMessages, ColorInterface::RED);
        $model = new ConsoleModel();
        $model->setErrorLevel(1);
        $model->setResult($validationMessages);
        $e->setResult($model);
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        // set a higher priority than default route listener so it gets triggered first
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), 10);
    }
}
