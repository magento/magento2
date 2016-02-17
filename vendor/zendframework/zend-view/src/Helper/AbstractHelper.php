<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Renderer\RendererInterface as Renderer;

abstract class AbstractHelper implements HelperInterface
{
    /**
     * View object instance
     *
     * @var Renderer
     */
    protected $view = null;

    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return AbstractHelper
     */
    public function setView(Renderer $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the view object
     *
     * @return null|Renderer
     */
    public function getView()
    {
        return $this->view;
    }
}
