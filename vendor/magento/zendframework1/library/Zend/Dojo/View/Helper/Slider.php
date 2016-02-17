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
 * Abstract class for Dojo Slider dijits
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
abstract class Zend_Dojo_View_Helper_Slider extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.Slider';

    /**
     * Required slider parameters
     * @var array
     */
    protected $_requiredParams = array('minimum', 'maximum', 'discreteValues');

    /**
     * Slider type -- vertical or horizontal
     * @var string
     */
    protected $_sliderType;

    /**
     * dijit.form.Slider
     *
     * @param  int $id
     * @param  mixed $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function prepareSlider($id, $value = null, array $params = array(), array $attribs = array())
    {
        $this->_sliderType = strtolower($this->_sliderType);

        // Prepare two items: a hidden element to store the value, and the slider
        $hidden = $this->_renderHiddenElement($id, $value);
        $hidden = preg_replace('/(name=")([^"]*)"/', 'id="$2" $1$2"', $hidden);

        foreach ($this->_requiredParams as $param) {
            if (!array_key_exists($param, $params)) {
                #require_once 'Zend/Dojo/View/Exception.php';
                throw new Zend_Dojo_View_Exception('prepareSlider() requires minimally the "minimum", "maximum", and "discreteValues" parameters');
            }
        }

        $content = '';
        $attribs['value'] = $value;

        if (!array_key_exists('onChange', $attribs)) {
            $attribs['onChange'] = "dojo.byId('" . $id . "').value = arguments[0];";
        }

        $id  = str_replace('][', '-', $id);
        $id  = str_replace(array('[', ']'), '-', $id);
        $id  = rtrim($id, '-');
        $id .= '-slider';

        switch ($this->_sliderType) {
            case 'horizontal':
                if (array_key_exists('topDecoration', $params)) {
                    $content .= $this->_prepareDecoration('topDecoration', $id, $params['topDecoration']);
                    unset($params['topDecoration']);
                }

                if (array_key_exists('bottomDecoration', $params)) {
                    $content .= $this->_prepareDecoration('bottomDecoration', $id, $params['bottomDecoration']);
                    unset($params['bottomDecoration']);
                }

                if (array_key_exists('leftDecoration', $params)) {
                    unset($params['leftDecoration']);
                }

                if (array_key_exists('rightDecoration', $params)) {
                    unset($params['rightDecoration']);
                }
                break;
            case 'vertical':
                if (array_key_exists('leftDecoration', $params)) {
                    $content .= $this->_prepareDecoration('leftDecoration', $id, $params['leftDecoration']);
                    unset($params['leftDecoration']);
                }

                if (array_key_exists('rightDecoration', $params)) {
                    $content .= $this->_prepareDecoration('rightDecoration', $id, $params['rightDecoration']);
                    unset($params['rightDecoration']);
                }

                if (array_key_exists('topDecoration', $params)) {
                    unset($params['topDecoration']);
                }

                if (array_key_exists('bottomDecoration', $params)) {
                    unset($params['bottomDecoration']);
                }
                break;
            default:
                #require_once 'Zend/Dojo/View/Exception.php';
                throw new Zend_Dojo_View_Exception('Invalid slider type; slider must be horizontal or vertical');
        }

        return $hidden . $this->_createLayoutContainer($id, $content, $params, $attribs);
    }

    /**
     * Prepare slider decoration
     *
     * @param  string $position
     * @param  string $id
     * @param  array $decInfo
     * @return string
     */
    protected function _prepareDecoration($position, $id, $decInfo)
    {
        if (!in_array($position, array('topDecoration', 'bottomDecoration', 'leftDecoration', 'rightDecoration'))) {
            return '';
        }

        if (!is_array($decInfo)
            || !array_key_exists('labels', $decInfo)
            || !is_array($decInfo['labels'])
        ) {
            return '';
        }

        $id .= '-' . $position;

        if (!array_key_exists('dijit', $decInfo)) {
            $dijit = 'dijit.form.' . ucfirst($this->_sliderType) . 'Rule';
        } else {
            $dijit = $decInfo['dijit'];
            if ('dijit.form.' != substr($dijit, 0, 10)) {
                $dijit = 'dijit.form.' . $dijit;
            }
        }

        $params  = array();
        $attribs = array();
        $labels  = $decInfo['labels'];
        if (array_key_exists('params', $decInfo)) {
            $params = $decInfo['params'];
        }
        if (array_key_exists('attribs', $decInfo)) {
            $attribs = $decInfo['attribs'];
        }

        $containerParams = null;
        if (array_key_exists('container', $params)) {
            $containerParams = $params['container'];
            unset($params['container']);
        }

        if (array_key_exists('labels', $params)) {
            $labelsParams = $params['labels'];
            unset($params['labels']);
        } else {
            $labelsParams = $params;
        }

        if (null === $containerParams) {
            $containerParams = $params;
        }

        $containerAttribs = null;
        if (array_key_exists('container', $attribs)) {
            $containerAttribs = $attribs['container'];
            unset($attribs['container']);
        }

        if (array_key_exists('labels', $attribs)) {
            $labelsAttribs = $attribs['labels'];
            unset($attribs['labels']);
        } else {
            $labelsAttribs = $attribs;
        }

        if (null === $containerAttribs) {
            $containerAttribs = $attribs;
        }

        $containerParams['container'] = $position;
        $labelsParams['container']    = $position;

        $labelList = $this->_prepareLabelsList($id, $labelsParams, $labelsAttribs, $labels);

        $dijit = 'dijit.form.' . ucfirst($this->_sliderType) . 'Rule';
        $containerAttribs['id'] = $id;
        $containerAttribs = $this->_prepareDijit($containerAttribs, $containerParams, 'layout', $dijit);
        $containerHtml = '<div' . $this->_htmlAttribs($containerAttribs) . "></div>\n";

        switch ($position) {
            case 'topDecoration':
            case 'leftDecoration':
                return $labelList . $containerHtml;
            case 'bottomDecoration':
            case 'rightDecoration':
                return $containerHtml . $labelList;
        }
    }

    /**
     * Prepare slider label list
     *
     * @param  string $id
     * @param  array $params
     * @param  array $attribs
     * @param  array $labels
     * @return string
     */
    protected function _prepareLabelsList($id, array $params, array $attribs, array $labels)
    {
        $attribs['id'] = $id . '-labels';
        $dijit = 'dijit.form.' . ucfirst($this->_sliderType) . 'RuleLabels';
        $attribs = $this->_prepareDijit($attribs, $params, 'layout', $dijit);

        return $this->view->htmlList($labels, true, $attribs);
    }
}
