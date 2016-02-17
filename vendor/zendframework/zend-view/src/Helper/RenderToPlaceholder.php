<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Model\ModelInterface;

/**
 * Renders a template and stores the rendered output as a placeholder
 * variable for later use.
 */
class RenderToPlaceholder extends AbstractHelper
{
    /**
     * Renders a template and stores the rendered output as a placeholder
     * variable for later use.
     *
     * @param string|ModelInterface $script      The template script to render
     * @param string                $placeholder The placeholder variable name in which to store the rendered output
     * @return void
     */
    public function __invoke($script, $placeholder)
    {
        $placeholderHelper = $this->view->plugin('placeholder');
        $placeholderHelper($placeholder)->captureStart();
        echo $this->view->render($script);
        $placeholderHelper($placeholder)->captureEnd();
    }
}
