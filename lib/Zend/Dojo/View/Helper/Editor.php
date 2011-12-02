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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Editor.php 20116 2010-01-07 14:18:34Z matthew $
 */

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/** Zend_Json */
#require_once 'Zend/Json.php';

/**
 * Dojo Editor dijit
 *
 * @uses       Zend_Dojo_View_Helper_Textarea
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_View_Helper_Editor extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * @param string Dijit type
     */
    protected $_dijit = 'dijit.Editor';

    /**
     * @var string Dijit module to load
     */
    protected $_module = 'dijit.Editor';

    /**
     * @var array Maps non-core plugin to module basename
     */
    protected $_pluginsModules = array(
        'createLink' => 'LinkDialog',
        'insertImage' => 'LinkDialog',
        'fontName' => 'FontChoice',
        'fontSize' => 'FontChoice',
        'formatBlock' => 'FontChoice',
        'foreColor' => 'TextColor',
        'hiliteColor' => 'TextColor'
    );

    /**
     * JSON-encoded parameters
     * @var array
     */
    protected $_jsonParams = array('captureEvents', 'events', 'plugins');

    /**
     * dijit.Editor
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params
     * @param  array $attribs
     * @return string
     */
    public function editor($id, $value = null, $params = array(), $attribs = array())
    {
        if (isset($params['plugins'])) {
            foreach ($this->_getRequiredModules($params['plugins']) as $module) {
                $this->dojo->requireModule($module);
            }
        }

        // Previous versions allowed specifying "degrade" to allow using a 
        // textarea instead of a div -- but this is insecure. Removing the 
        // parameter if set to prevent its injection in the dijit.
        if (isset($params['degrade'])) {
            unset($params['degrade']);
        }

        $hiddenName = $id;
        if (array_key_exists('id', $attribs)) {
            $hiddenId = $attribs['id'];
        } else {
            $hiddenId = $hiddenName;
        }
        $hiddenId = $this->_normalizeId($hiddenId);

        $textareaName = $this->_normalizeEditorName($hiddenName);
        $textareaId   = $hiddenId . '-Editor';

        $hiddenAttribs = array(
            'id'    => $hiddenId,
            'name'  => $hiddenName,
            'value' => $value,
            'type'  => 'hidden',
        );
        $attribs['id'] = $textareaId;

        $this->_createGetParentFormFunction();
        $this->_createEditorOnSubmit($hiddenId, $textareaId);

        $attribs = $this->_prepareDijit($attribs, $params, 'textarea');

        $html  = '<input' . $this->_htmlAttribs($hiddenAttribs) . $this->getClosingBracket();
        $html .= '<div' . $this->_htmlAttribs($attribs) . '>'
               . $value
               . "</div>\n";

        // Embed a textarea in a <noscript> tag to allow for graceful 
        // degradation
        $html .= '<noscript>'
               . $this->view->formTextarea($hiddenId, $value, $attribs)
               . '</noscript>';

        return $html;
    }

    /**
     * Generates the list of required modules to include, if any is needed.
     *
     * @param array $plugins plugins to include
     * @return array
     */
    protected function _getRequiredModules(array $plugins)
    {
        $modules = array();
        foreach ($plugins as $commandName) {
            if (isset($this->_pluginsModules[$commandName])) {
                $pluginName = $this->_pluginsModules[$commandName];
                $modules[] = 'dijit._editor.plugins.' . $pluginName;
            }
        }

        return array_unique($modules);
    }

    /**
     * Normalize editor element name
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeEditorName($name)
    {
        if ('[]' == substr($name, -2)) {
            $name = substr($name, 0, strlen($name) - 2);
            $name .= '[Editor][]';
        } else {
            $name .= '[Editor]';
        }
        return $name;
    }

    /**
     * Create onSubmit binding for element
     *
     * @param  string $hiddenId
     * @param  string $editorId
     * @return void
     */
    protected function _createEditorOnSubmit($hiddenId, $editorId)
    {
        $this->dojo->onLoadCaptureStart();
        echo <<<EOJ
function() {
    var form = zend.findParentForm(dojo.byId('$hiddenId'));
    dojo.connect(form, 'submit', function(e) {
        dojo.byId('$hiddenId').value = dijit.byId('$editorId').getValue(false);
    });
}
EOJ;
        $this->dojo->onLoadCaptureEnd();
    }
}
