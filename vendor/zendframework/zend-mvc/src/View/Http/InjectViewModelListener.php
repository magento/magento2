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
use Zend\View\Model\ClearableModelInterface;
use Zend\View\Model\ModelInterface as ViewModel;

class InjectViewModelListener extends AbstractListenerAggregate
{
    /**
     * FilterInterface/inflector used to normalize names for use as template identifiers
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectViewModel'), -100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'injectViewModel'), -100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'injectViewModel'), -100);
    }

    /**
     * Insert the view model into the event
     *
     * Inspects the MVC result; if it's a view model, it then either (a) adds
     * it as a child to the default, composed view model, or (b) replaces it
     * if the result is marked as terminable.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if (!$result instanceof ViewModel) {
            return;
        }

        $model = $e->getViewModel();

        if ($result->terminate()) {
            $e->setViewModel($result);
            return;
        }

        if ($e->getError() && $model instanceof ClearableModelInterface) {
            $model->clearChildren();
        }

        $model->addChild($result);
    }
}
