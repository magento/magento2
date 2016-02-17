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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_View_Helper_HtmlElement */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Dojo dijit base class
 *
 * @uses       Zend_View_Helper_Abstract
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
abstract class Zend_Dojo_View_Helper_Dijit extends Zend_View_Helper_HtmlElement
{
    /**
     * @var Zend_Dojo_View_Helper_Dojo_Container
     */
    public $dojo;

    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit;

    /**
     * Element type
     * @var string
     */
    protected $_elementType;

    /**
     * Parameters that should be JSON encoded
     * @var array
     */
    protected $_jsonParams = array('constraints');

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module;

    /**
     * Root node element type for layout elements
     * @var string
     */
    protected $_rootNode = 'div';

    /**
     * Set view
     *
     * Set view and enable dojo
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Dojo_View_Helper_Dijit
     */
    public function setView(Zend_View_Interface $view)
    {
        parent::setView($view);
        $this->dojo = $this->view->dojo();
        $this->dojo->enable();
        return $this;
    }


    /**
     * Get root node type
     *
     * @return string
     */
    public function getRootNode()
    {
        return $this->_rootNode;
    }

    /**
     * Set root node type
     *
     * @param  string $value
     * @return Zend_Dojo_View_Helper_Dijit
     */
    public function setRootNode($value)
    {
        $this->_rootNode = $value;
        return $this;
    }

    /**
     * Whether or not to use declarative dijit creation
     *
     * @return bool
     */
    protected function _useDeclarative()
    {
        return Zend_Dojo_View_Helper_Dojo::useDeclarative();
    }

    /**
     * Whether or not to use programmatic dijit creation
     *
     * @return bool
     */
    protected function _useProgrammatic()
    {
        return Zend_Dojo_View_Helper_Dojo::useProgrammatic();
    }

    /**
     * Whether or not to use programmatic dijit creation w/o script creation
     *
     * @return bool
     */
    protected function _useProgrammaticNoScript()
    {
        return Zend_Dojo_View_Helper_Dojo::useProgrammaticNoScript();
    }

    /**
     * Create a layout container
     *
     * @param  int $id
     * @param  string $content
     * @param  array $params
     * @param  array $attribs
     * @param  string|null $dijit
     * @return string
     */
    protected function _createLayoutContainer($id, $content, array $params, array $attribs, $dijit = null)
    {
        $attribs['id'] = $id;
        $attribs = $this->_prepareDijit($attribs, $params, 'layout', $dijit);

        $nodeType = $this->getRootNode();
        $html = '<' . $nodeType . $this->_htmlAttribs($attribs) . '>'
              . $content
              . "</$nodeType>\n";

        return $html;
    }

    /**
     * Create HTML representation of a dijit form element
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params
     * @param  array $attribs
     * @param  string|null $dijit
     * @return string
     */
    public function _createFormElement($id, $value, array $params, array $attribs, $dijit = null)
    {
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }
        $attribs['name']  = $id;
        $attribs['value'] = (string) $value;
        $attribs['type']  = $this->_elementType;

        $attribs = $this->_prepareDijit($attribs, $params, 'element', $dijit);

        $html = '<input'
              . $this->_htmlAttribs($attribs)
              . $this->getClosingBracket();
        return $html;
    }

    /**
     * Merge attributes and parameters
     *
     * Also sets up requires
     *
     * @param  array $attribs
     * @param  array $params
     * @param  string $type
     * @param  string $dijit Dijit type to use (otherwise, pull from $_dijit)
     * @return array
     */
    protected function _prepareDijit(array $attribs, array $params, $type, $dijit = null)
    {
        $this->dojo->requireModule($this->_module);

        switch ($type) {
            case 'layout':
                $stripParams = array('id');
                break;
            case 'element':
                $stripParams = array('id', 'name', 'value', 'type');
                foreach (array('checked', 'disabled', 'readonly') as $attrib) {
                    if (array_key_exists($attrib, $attribs)) {
                        if ($attribs[$attrib]) {
                            $attribs[$attrib] = $attrib;
                        } else {
                            unset($attribs[$attrib]);
                        }
                    }
                }
                break;
            case 'textarea':
                $stripParams = array('id', 'name', 'type', 'degrade');
                break;
            default:
        }

        foreach ($stripParams as $param) {
            if (array_key_exists($param, $params)) {
                unset($params[$param]);
            }
        }

        // Normalize constraints, if present
        foreach ($this->_jsonParams as $param) {
            if (array_key_exists($param, $params)) {
                #require_once 'Zend/Json.php';

                if (is_array($params[$param])) {
                    $values = array();
                    foreach ($params[$param] as $key => $value) {
                        if (!is_scalar($value)) {
                            continue;
                        }
                        $values[$key] = $value;
                    }
                } elseif (is_string($params[$param])) {
                    $values = (array) $params[$param];
                } else {
                    $values = array();
                }
                $values = Zend_Json::encode($values);
                if ($this->_useDeclarative()) {
                    $values = str_replace('"', "'", $values);
                }
                $params[$param] = $values;
            }
        }

        $dijit = (null === $dijit) ? $this->_dijit : $dijit;
        if ($this->_useDeclarative()) {
            $attribs = array_merge($attribs, $params);
            if (isset($attribs['required'])) {
                $attribs['required'] = ($attribs['required']) ? 'true' : 'false';
            }
            $attribs['dojoType'] = $dijit;
        } elseif (!$this->_useProgrammaticNoScript()) {
            $this->_createDijit($dijit, $attribs['id'], $params);
        }

        return $attribs;
    }

    /**
     * Create a dijit programmatically
     *
     * @param  string $dijit
     * @param  string $id
     * @param  array $params
     * @return void
     */
    protected function _createDijit($dijit, $id, array $params)
    {
        $params['dojoType'] = $dijit;

        array_walk_recursive($params, array($this, '_castBoolToString'));

        $this->dojo->setDijit($id, $params);
    }

    /**
     * Cast a boolean to a string value
     *
     * @param  mixed $item
     * @param  string $key
     * @return void
     */
    protected function _castBoolToString(&$item, $key)
    {
        if (!is_bool($item)) {
            return;
        }
        $item = ($item) ? "true" : "false";
    }

    /**
     * Render a hidden element to hold a value
     *
     * @param  string $id
     * @param  string|int|float $value
     * @return string
     */
    protected function _renderHiddenElement($id, $value)
    {
        $hiddenAttribs = array(
            'name'  => $id,
            'value' => (string) $value,
            'type'  => 'hidden',
        );
        return '<input' . $this->_htmlAttribs($hiddenAttribs) . $this->getClosingBracket();
    }

    /**
     * Create JS function for retrieving parent form
     *
     * @return void
     */
    protected function _createGetParentFormFunction()
    {
        $function =<<<EOJ
if (zend == undefined) {
    var zend = {};
}
zend.findParentForm = function(elementNode) {
    while (elementNode.nodeName.toLowerCase() != 'form') {
        elementNode = elementNode.parentNode;
    }
    return elementNode;
};
EOJ;

        $this->dojo->addJavascript($function);
    }
}
