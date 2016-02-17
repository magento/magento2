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

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/**
 * Dojo ComboBox dijit
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_ComboBox extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.ComboBox';

    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.ComboBox';

    /**
     * dijit.form.ComboBox
     *
     * @param  int $id
     * @param  mixed $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @param  array|null $options Select options
     * @return string
     */
    public function comboBox($id, $value = null, array $params = array(), array $attribs = array(), array $options = null)
    {
        $html = '';
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }
        if (array_key_exists('store', $params) && is_array($params['store'])) {
            // using dojo.data datastore
            if (false !== ($store = $this->_renderStore($params['store'], $id))) {
                $params['store'] = $params['store']['store'];
                if (is_string($store)) {
                    $html .= $store;
                }
                $html .= $this->_createFormElement($id, $value, $params, $attribs);
                return $html;
            }
            unset($params['store']);
        } elseif (array_key_exists('store', $params)) {
            if (array_key_exists('storeType', $params)) {
                $storeParams = array(
                    'store' => $params['store'],
                    'type'  => $params['storeType'],
                );
                unset($params['storeType']);
                if (array_key_exists('storeParams', $params)) {
                    $storeParams['params'] = $params['storeParams'];
                    unset($params['storeParams']);
                }
                if (false !== ($store = $this->_renderStore($storeParams, $id))) {
                    if (is_string($store)) {
                        $html .= $store;
                    }
                }
            }
            $html .= $this->_createFormElement($id, $value, $params, $attribs);
            return $html;
        }

        // required for correct type casting in declerative mode
        if (isset($params['autocomplete'])) {
            $params['autocomplete'] = ($params['autocomplete']) ? 'true' : 'false';
        }
        // do as normal select
        $attribs = $this->_prepareDijit($attribs, $params, 'element');
        return $this->view->formSelect($id, $value, $attribs, $options);
    }

    /**
     * Render data store element
     *
     * Renders to dojo view helper
     *
     * @param  array $params
     * @return string|false
     */
    protected function _renderStore(array $params, $id)
    {
        if (!array_key_exists('store', $params) || !array_key_exists('type', $params)) {
            return false;
        }

        $this->dojo->requireModule($params['type']);

        $extraParams = array();
        $storeParams = array(
            'dojoType' => $params['type'],
            'jsId'     => $params['store'],
        );

        if (array_key_exists('params', $params)) {
            $storeParams = array_merge($storeParams, $params['params']);
            $extraParams = $params['params'];
        }

        if ($this->_useProgrammatic()) {
            if (!$this->_useProgrammaticNoScript()) {
                #require_once 'Zend/Json.php';
                $this->dojo->addJavascript('var ' . $storeParams['jsId'] . ";\n");
                $js = $storeParams['jsId'] . ' = '
                    . 'new ' . $storeParams['dojoType'] . '('
                    .     Zend_Json::encode($extraParams)
                    . ");\n";
                $js = "function() {\n$js\n}";
                $this->dojo->_addZendLoad($js);
            }
            return true;
        }

        return '<div' . $this->_htmlAttribs($storeParams) . '></div>';
    }
}
