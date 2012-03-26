<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Base html block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Block_Template extends Mage_Core_Block_Abstract
{
    const XML_PATH_DEBUG_TEMPLATE_HINTS         = 'dev/debug/template_hints';
    const XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS  = 'dev/debug/template_hints_blocks';
    const XML_PATH_TEMPLATE_ALLOW_SYMLINK       = 'dev/template/allow_symlink';

    /**
     * View scripts directory
     *
     * @var string
     */
    protected $_viewDir = '';

    /**
     * Assigned variables for view
     *
     * @var array
     */
    protected $_viewVars = array();

    protected $_baseUrl;

    protected $_jsUrl;

    /**
     * Is allowed symlinks flag
     *
     * @var bool
     */
    protected $_allowSymlinks = null;

    protected static $_showTemplateHints;
    protected static $_showTemplateHintsBlocks;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template;

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();

        /*
         * In case template was passed through constructor
         * we assign it to block's property _template
         * Mainly for those cases when block created
         * not via Mage_Core_Model_Layout::addBlock()
         */
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Set path to template used for generating block's output.
     *
     * @param string $template
     * @return Mage_Core_Block_Template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array('_module' => $this->getModuleName());
        $area = $this->getArea();
        if ($area) {
            $params['_area'] = $area;
        }
        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        return $templateName;
    }

    /**
     * Get design area
     * @return string
     */
    public function getArea()
    {
        return $this->_getData('area');
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  Mage_Core_Block_Template
     */
    public function assign($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        }
        else {
            $this->_viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Set template location directory
     *
     * @param string $dir
     * @return Mage_Core_Block_Template
     */
    public function setScriptPath($dir)
    {
        $scriptPath = realpath($dir);
        if (strpos($scriptPath, realpath(Mage::getBaseDir('design'))) === 0 || $this->_getAllowSymlinks()) {
            $this->_viewDir = $dir;
        } else {
            Mage::log('Not valid script path:' . $dir, Zend_Log::CRIT, null, null, true);
        }
        return $this;
    }

    /**
     * Check if direct output is allowed for block
     *
     * @return bool
     */
    public function getDirectOutput()
    {
        if ($this->getLayout()) {
            return $this->getLayout()->isDirectOutput();
        }
        return false;
    }

    public function getShowTemplateHints()
    {
        if (is_null(self::$_showTemplateHints)) {
            self::$_showTemplateHints = Mage::getStoreConfig(self::XML_PATH_DEBUG_TEMPLATE_HINTS)
                && Mage::helper('Mage_Core_Helper_Data')->isDevAllowed();
            self::$_showTemplateHintsBlocks = Mage::getStoreConfig(self::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS)
                && Mage::helper('Mage_Core_Helper_Data')->isDevAllowed();
        }
        return self::$_showTemplateHints;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        Magento_Profiler::start('TEMPLATE:' . $fileName);

        // EXTR_SKIP protects from overriding
        // already defined variables
        extract ($this->_viewVars, EXTR_SKIP);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo <<<HTML
<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'"
onmouseout="this.style.zIndex='998'" title="{$fileName}">{$fileName}</div>
HTML;
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo <<<HTML
<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'" onmouseout="this.style.zIndex='998'"
title="{$thisClass}">{$thisClass}</div>
HTML;
            }
        }

        try {
            $templateFile = realpath($fileName);
            if (strpos($templateFile, Mage::getBaseDir('app')) === 0
                || strpos($templateFile, realpath($this->_viewDir)) === 0 || $this->_getAllowSymlinks()
            ) {
                include $templateFile;
            } else {
                Mage::log("Invalid template file: '{$fileName}'", Zend_Log::CRIT, null, null, true);
            }

        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        Magento_Profiler::stop('TEMPLATE:' . $fileName);
        return $html;
    }

    /**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        $this->setScriptPath(Mage::getBaseDir('design'));
        $html = $this->fetchView($this->getTemplateFile());
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->renderView();
        return $html;
    }

    /**
     * Get base url of the application
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (!$this->_baseUrl) {
            $this->_baseUrl = Mage::getBaseUrl();
        }
        return $this->_baseUrl;
    }

    /**
     * Get data from specified object
     *
     * @param Varien_Object $object
     * @param string $key
     * @return mixed
     */
    public function getObjectData(Varien_Object $object, $key)
    {
        return $object->getDataUsingMethod((string)$key);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate()
        );
    }

    /**
     * Get is allowed symliks flag
     *
     * @return bool
     */
    protected function _getAllowSymlinks()
    {
        if (is_null($this->_allowSymlinks)) {
            $this->_allowSymlinks = Mage::getStoreConfigFlag(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK);
        }
        return $this->_allowSymlinks;
    }
}
