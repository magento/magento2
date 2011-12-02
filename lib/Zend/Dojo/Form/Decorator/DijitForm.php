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
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Decorator_DijitContainer */
#require_once 'Zend/Dojo/Form/Decorator/DijitContainer.php';

/**
 * Zend_Dojo_Form_Decorator_DijitForm
 *
 * Render a dojo form dijit via a view helper
 *
 * Accepts the following options:
 * - helper:    the name of the view helper to use
 *
 * @package    Zend_Dojo
 * @subpackage Form_Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DijitForm.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Decorator_DijitForm extends Zend_Dojo_Form_Decorator_DijitContainer
{
    /**
     * Render a form
     *
     * Replaces $content entirely from currently set element.
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $dijitParams = $this->getDijitParams();
        $attribs     = array_merge($this->getAttribs(), $this->getOptions());

        return $view->form($element->getName(), $attribs, $content);
    }
}
