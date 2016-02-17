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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
#require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Dojo_Form_Decorator_DijitContainer
 *
 * Render a dojo dijit layout container via a view helper
 *
 * Accepts the following options:
 * - helper:    the name of the view helper to use
 *
 * Assumes the view helper accepts four parameters, the id, content, dijit
 * parameters, and (X)HTML attributes; these will be provided by the element.
 *
 * @uses       Zend_Form_Decorator_Abstract
 * @package    Zend_Dojo
 * @subpackage Form_Decorator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Dojo_Form_Decorator_DijitContainer extends Zend_Form_Decorator_Abstract
{
    /**
     * View helper
     * @var string
     */
    protected $_helper;

    /**
     * Element attributes
     * @var array
     */
    protected $_attribs;

    /**
     * Dijit option parameters
     * @var array
     */
    protected $_dijitParams;

    /**
     * Container title
     * @var string
     */
    protected $_title;

    /**
     * Get view helper for rendering container
     *
     * @return string
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            #require_once 'Zend/Form/Decorator/Exception.php';
            throw new Zend_Form_Decorator_Exception('No view helper specified fo DijitContainer decorator');
        }
        return $this->_helper;
    }

    /**
     * Get element attributes
     *
     * @return array
     */
    public function getAttribs()
    {
        if (null === $this->_attribs) {
            $attribs = $this->getElement()->getAttribs();
            if (array_key_exists('dijitParams', $attribs)) {
                unset($attribs['dijitParams']);
            }
            $this->_attribs = $attribs;
        }
        return $this->_attribs;
    }

    /**
     * Get dijit option parameters
     *
     * @return array
     */
    public function getDijitParams()
    {
        if (null === $this->_dijitParams) {
            $attribs = $this->getElement()->getAttribs();
            if (array_key_exists('dijitParams', $attribs)) {
                $this->_dijitParams = $attribs['dijitParams'];
            } else {
                $this->_dijitParams = array();
            }

            $options = $this->getOptions();
            if (array_key_exists('dijitParams', $options)) {
                $this->_dijitParams = array_merge($this->_dijitParams, $options['dijitParams']);
                $this->removeOption('dijitParams');
            }
        }

        // Ensure we have a title param
        if (!array_key_exists('title', $this->_dijitParams)) {
            $this->_dijitParams['title'] = $this->getTitle();
        }

        return $this->_dijitParams;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        if (null === $this->_title) {
            $title = null;
            if (null !== ($element = $this->getElement())) {
                if (method_exists($element, 'getLegend')) {
                    $title = $element->getLegend();
                }
            }
            if (empty($title) && (null !== ($title = $this->getOption('legend')))) {
                $this->removeOption('legend');
            }
            if (empty($title) && (null !== ($title = $this->getOption('title')))) {
                $this->removeOption('title');
            }

            if (!empty($title)) {
                if (null !== ($translator = $element->getTranslator())) {
                    $title = $translator->translate($title);
                }
                $this->_title = $title;
            }
        }

        return (empty($this->_title) ? '' : $this->_title);
    }

    /**
     * Render a dijit layout container
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

        if (array_key_exists('legend', $attribs)) {
            if (!array_key_exists('title', $dijitParams) || empty($dijitParams['title'])) {
                $dijitParams['title'] = $attribs['legend'];
            }
            unset($attribs['legend']);
        }

        $helper      = $this->getHelper();
        $id          = $element->getId() . '-' . $helper;

        if ($view->dojo()->hasDijit($id)) {
            trigger_error(sprintf('Duplicate dijit ID detected for id "%s; temporarily generating uniqid"', $id), E_USER_WARNING);
            $base = $id;
            do {
                $id = $base . '-' . uniqid();
            } while ($view->dojo()->hasDijit($id));
        }

        return $view->$helper($id, $content, $dijitParams, $attribs);
    }
}
