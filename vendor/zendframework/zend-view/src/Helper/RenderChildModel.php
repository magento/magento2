<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Exception;
use Zend\View\Model\ModelInterface as Model;

/**
 * Helper for rendering child view models
 *
 * Finds children matching "capture-to" values, and renders them using the
 * composed view instance.
 */
class RenderChildModel extends AbstractHelper
{
    /**
     * Current view model
     *
     * @var Model
     */
    protected $current;

    /**
     * View model helper instance
     *
     * @var ViewModel
     */
    protected $viewModelHelper;

    /**
     * Invoke as a function
     *
     * Proxies to {render()}.
     *
     * @param  string $child
     * @return string
     */
    public function __invoke($child)
    {
        return $this->render($child);
    }

    /**
     * Render a model
     *
     * If a matching child model is found, it is rendered. If not, an empty
     * string is returned.
     *
     * @param  string $child
     * @return string
     */
    public function render($child)
    {
        $model = $this->findChild($child);
        if (!$model) {
            return '';
        }

        $current = $this->current;
        $view    = $this->getView();
        $return  = $view->render($model);
        $helper  = $this->getViewModelHelper();
        $helper->setCurrent($current);

        return $return;
    }

    /**
     * Find the named child model
     *
     * Iterates through the current view model, looking for a child model that
     * has a captureTo value matching the requested $child. If found, that child
     * model is returned; otherwise, a boolean false is returned.
     *
     * @param  string $child
     * @return false|Model
     */
    protected function findChild($child)
    {
        $this->current = $model = $this->getCurrent();
        foreach ($model->getChildren() as $childModel) {
            if ($childModel->captureTo() == $child) {
                return $childModel;
            }
        }

        return false;
    }

    /**
     * Get the current view model
     *
     * @throws Exception\RuntimeException
     * @return null|Model
     */
    protected function getCurrent()
    {
        $helper = $this->getViewModelHelper();
        if (!$helper->hasCurrent()) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no view model currently registered in renderer; cannot query for children',
                __METHOD__
            ));
        }

        return $helper->getCurrent();
    }

    /**
     * Retrieve the view model helper
     *
     * @return ViewModel
     */
    protected function getViewModelHelper()
    {
        if ($this->viewModelHelper) {
            return $this->viewModelHelper;
        }

        if (method_exists($this->getView(), 'plugin')) {
            $this->viewModelHelper = $this->view->plugin('view_model');
        }

        return $this->viewModelHelper;
    }
}
