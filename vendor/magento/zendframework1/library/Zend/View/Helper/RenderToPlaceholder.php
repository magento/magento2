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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Renders a template and stores the rendered output as a placeholder
 * variable for later use.
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_View_Helper_RenderToPlaceholder extends Zend_View_Helper_Abstract
{

    /**
     * Renders a template and stores the rendered output as a placeholder
     * variable for later use.
     *
     * @param string $script The template script to render
     * @param string $placeholder The placeholder variable name in which to store the rendered output
     * @return void
     */
    public function renderToPlaceholder($script, $placeholder)
    {
        $this->view->placeholder($placeholder)->captureStart();
        echo $this->view->render($script);
        $this->view->placeholder($placeholder)->captureEnd();
    }
}
