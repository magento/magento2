<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Http;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

class CreateViewModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'createViewModelFromArray'),  -80);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'createViewModelFromNull'),   -80);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if an assoc array is detected
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function createViewModelFromArray(MvcEvent $e)
    {
        $result = $e->getResult();
        if (!ArrayUtils::hasStringKeys($result, true)) {
            return;
        }

        $model = new ViewModel($result);
        $e->setResult($model);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if null is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function createViewModelFromNull(MvcEvent $e)
    {
        $result = $e->getResult();
        if (null !== $result) {
            return;
        }

        $model = new ViewModel;
        $e->setResult($model);
    }
}
