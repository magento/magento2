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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Abstract master class for extension.
 */
#require_once 'Zend/View/Abstract.php';


/**
 * Concrete class for handling view scripts.
 *
 * @category  Zend
 * @package   Zend_View
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 *
 * Convenience methods for build in helpers (@see __call):
 *
 * @method string baseUrl($file = null)
 * @method string currency($value = null, $currency = null)
 * @method Zend_View_Helper_Cycle cycle(array $data = array(), $name = Zend_View_Helper_Cycle::DEFAULT_NAME)
 * @method Zend_View_Helper_Doctype doctype($doctype = null)
 * @method string fieldset($name, $content, $attribs = null)
 * @method string form($name, $attribs = null, $content = false)
 * @method string formButton($name, $value = null, $attribs = null)
 * @method string formCheckbox($name, $value = null, $attribs = null, array $checkedOptions = null)
 * @method string formErrors($errors, array $options = null)
 * @method string formFile($name, $attribs = null)
 * @method string formHidden($name, $value = null, array $attribs = null)
 * @method string formImage($name, $value = null, $attribs = null)
 * @method string formLabel($name, $value = null, array $attribs = null)
 * @method string formMultiCheckbox($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
 * @method string formNote($name, $value = null)
 * @method string formPassword($name, $value = null, $attribs = null)
 * @method string formRadio($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
 * @method string formReset($name = '', $value = 'Reset', $attribs = null)
 * @method string formSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
 * @method string formSubmit($name, $value = null, $attribs = null)
 * @method string formText($name, $value = null, $attribs = null)
 * @method string formTextarea($name, $value = null, $attribs = null)
 * @method Zend_View_Helper_Gravatar gravatar($email = "", $options = array(), $attribs = array())
 * @method Zend_View_Helper_HeadLink headLink(array $attributes = null, $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
 * @method Zend_View_Helper_HeadMeta headMeta($content = null, $keyValue = null, $keyType = 'name', $modifiers = array(), $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
 * @method Zend_View_Helper_HeadScript headScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method Zend_View_Helper_HeadStyle headStyle($content = null, $placement = 'APPEND', $attributes = array())
 * @method Zend_View_Helper_HeadTitle headTitle($title = null, $setType = null)
 * @method string htmlFlash($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlList(array $items, $ordered = false, $attribs = false, $escape = true)
 * @method string htmlObject($data, $type, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlPage($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlQuicktime($data, array $attribs = array(), array $params = array(), $content = null)
 * @method Zend_View_Helper_InlineScript inlineScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method string|void json($data, $keepLayouts = false, $encodeData = true)
 * @method Zend_View_Helper_Layout layout()
 * @method Zend_View_Helper_Navigation navigation(Zend_Navigation_Container $container = null)
 * @method string paginationControl(Zend_Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
 * @method string partial($name = null, $module = null, $model = null)
 * @method string partialLoop($name = null, $module = null, $model = null)
 * @method Zend_View_Helper_Placeholder_Container_Abstract placeholder($name)
 * @method void renderToPlaceholder($script, $placeholder)
 * @method string serverUrl($requestUri = null)
 * @method string translate($messageid = null)
 * @method string url(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
 * @method Zend_Http_UserAgent userAgent(Zend_Http_UserAgent $userAgent = null)
 */
class Zend_View extends Zend_View_Abstract
{
    /**
     * Whether or not to use streams to mimic short tags
     * @var bool
     */
    private $_useViewStream = false;

    /**
     * Whether or not to use stream wrapper if short_open_tag is false
     * @var bool
     */
    private $_useStreamWrapper = false;

    /**
     * Constructor
     *
     * Register Zend_View_Stream stream wrapper if short tags are disabled.
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        $this->_useViewStream = (bool) ini_get('short_open_tag') ? false : true;
        if ($this->_useViewStream) {
            if (!in_array('zend.view', stream_get_wrappers())) {
                #require_once 'Zend/View/Stream.php';
                stream_wrapper_register('zend.view', 'Zend_View_Stream');
            }
        }

        if (array_key_exists('useStreamWrapper', $config)) {
            $this->setUseStreamWrapper($config['useStreamWrapper']);
        }

        parent::__construct($config);
    }

    /**
     * Set flag indicating if stream wrapper should be used if short_open_tag is off
     *
     * @param  bool $flag
     * @return Zend_View
     */
    public function setUseStreamWrapper($flag)
    {
        $this->_useStreamWrapper = (bool) $flag;
        return $this;
    }

    /**
     * Should the stream wrapper be used if short_open_tag is off?
     *
     * @return bool
     */
    public function useStreamWrapper()
    {
        return $this->_useStreamWrapper;
    }

    /**
     * Includes the view script in a scope with only public $this variables.
     *
     * @param string The view script to execute.
     */
    protected function _run()
    {
        if ($this->_useViewStream && $this->useStreamWrapper()) {
            include 'zend.view://' . func_get_arg(0);
        } else {
            include func_get_arg(0);
        }
    }
}
