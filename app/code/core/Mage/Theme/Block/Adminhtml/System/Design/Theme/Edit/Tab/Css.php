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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme form, Css editor tab
 *
 * @method Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css setFiles(array $files)
 * @method array getFiles()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
    extends Mage_Backend_Block_Widget_Form
    implements Mage_Backend_Block_Widget_Tab_Interface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Uploader service
     *
     * @var Mage_Theme_Model_Uploader_Service
     */
    protected $_uploaderService;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Logger $logger
     * @param Magento_Filesystem $filesystem
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Theme_Model_Uploader_Service $uploaderService
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Logger $logger,
        Magento_Filesystem $filesystem,
        Magento_ObjectManager $objectManager,
        Mage_Theme_Model_Uploader_Service $uploaderService,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data
        );
        $this->_objectManager = $objectManager;
        $this->_uploaderService = $uploaderService;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $this->_addThemeCssFieldset();
        $this->_addCustomCssFieldset();

        $formData['custom_css_content'] = $this->_getCurrentTheme()->getCustomCssFile()->getContent();
        /** @var $session Mage_Backend_Model_Session */
        $session = $this->_objectManager->get('Mage_Backend_Model_Session');
        $cssFileContent = $session->getThemeCustomCssData();
        if ($cssFileContent) {
            $formData['custom_css_content'] = $cssFileContent;
            $session->unsThemeCustomCssData();
        }
        $form->addValues($formData);
        return $this;
    }

    /**
     * Set theme css fieldset
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected function _addThemeCssFieldset()
    {
        $form = $this->getForm();
        $themeFieldset = $form->addFieldset('theme_css', array(
            'legend' => $this->__('Theme CSS'),
            'class'  => 'fieldset-wide'
        ));
        $this->_addElementTypes($themeFieldset);
        foreach ($this->_getGroupedFiles() as $groupName => $group) {
            $themeFieldset->addField('theme_css_view_' . $groupName, 'links', array(
                'label'       => $groupName,
                'title'       => $groupName,
                'name'        => 'links',
                'values'      => $group,
            ));
        }

        return $this;
    }

    /**
     * Prepare file items for output on page for download
     *
     * @param string $fileTitle
     * @param string $filePath
     * @return array
     */
    protected function _getThemeCss($fileTitle, $filePath)
    {
        return array(
            'href'      => $this->getUrl('*/*/downloadCss', array(
                'theme_id' => $this->_getCurrentTheme()->getId(),
                'file'     => $this->_helperFactory->get('Mage_Theme_Helper_Data')->urlEncode($fileTitle))
            ),
            'label'     => $fileTitle,
            'title'     => $filePath,
            'delimiter' => '<br />'
        );
    }

    /**
     * Set custom css fieldset
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected function _addCustomCssFieldset()
    {
        $form = $this->getForm();
        $themeFieldset = $form->addFieldset('custom_css', array(
            'legend' => $this->__('Custom CSS'),
            'class'  => 'fieldset-wide'
        ));

        $themeFieldset->addField('css_file_uploader', 'file', array(
            'name'     => 'css_file_uploader',
            'label'    => $this->__('Select CSS File to Upload'),
            'title'    => $this->__('Select CSS File to Upload'),
            'note'     => $this->__('Allowed file types *.css.')
                . ' ' . $this->__('The file you upload will replace the existing custom.css file (shown below).')
        ));

        $themeFieldset->addField('css_uploader_button', 'button', array(
            'name'     => 'css_uploader_button',
            'value'    => $this->__('Upload CSS File'),
            'disabled' => 'disabled',
        ));

        $themeFieldset->addField('custom_css_content', 'textarea', array(
            'label'  => $this->__('Edit custom.css'),
            'title'  => $this->__('Edit custom.css'),
            'name'   => 'custom_css_content',
        ));

        return $this;
    }

    /**
     * Get current theme
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _getCurrentTheme()
    {
        return Mage::registry('current_theme');
    }

    /**
     * Set additional form field type for theme preview image
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $element = Mage::getConfig()
            ->getBlockClassName('Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Form_Element_Links');
        return array('links' => $element);
    }

    /**
     * Get files by groups
     *
     * @return array
     */
    protected function _getGroupedFiles()
    {
        $jsDir = $this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB);
        $codeDir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES);

        $groups = array();
        $themes = array();
        foreach ($this->getFiles() as $fileTitle => $filePath) {
            /** @var $theme Mage_Core_Model_Theme */
            list($group, $theme) = $this->_getGroup($filePath);
            if ($theme) {
                $themes[$theme->getThemeId()] = $theme;
            }

            if (!isset($groups[$group])) {
                $groups[$group] = array();
            }
            $groups[$group][] = $this->_getThemeCss($fileTitle, $filePath);
        }

        if (count($themes) > 1) {
            $this->_sortThemes($themes);
        }

        $order = array_merge(array($codeDir, $jsDir), array_map(function ($theme) {
            /** @var $theme Mage_Core_Model_Theme */
            return $theme->getThemeId();
        }, $themes));
        $groups = $this->_sortArrayByArray($groups, $order);

        $labels = $this->_getGroupLabels($themes);
        foreach ($groups as $key => $group) {
            usort($group, array($this, '_sortGroupFiles'));
            $groups[$labels[$key]] = $group;
            unset($groups[$key]);
        }
        return $groups;
    }

    /**
     * Sort files inside group
     *
     * @param array $item1
     * @param array $item2
     * @return int
     */
    protected function _sortGroupFiles ($item1, $item2)
    {
        $hasModuleContext = strpos($item1['label'], '::') !== false;
        $hasModuleContext2 = strpos($item2['label'], '::') !== false;

        if ($hasModuleContext && $hasModuleContext2) {
            $result = strcmp($item1['label'], $item2['label']);
        } elseif (!$hasModuleContext && !$hasModuleContext2) {
            $result = strcmp($item1['label'], $item2['label']);
        } elseif ($hasModuleContext) {
            //case when first item has module context and second item doesn't
            $result = 1;
        } else {
            //case when second item has module context and first item doesn't
            $result = -1;
        }
        return $result;
    }

    /**
     * Get group by filename
     *
     * @param string $filename
     * @return array
     */
    protected function _getGroup($filename)
    {
        $designDir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $jsDir = $this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB);
        $codeDir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES);

        $group = null;
        $theme = null;
        if (substr($filename, 0, strlen($designDir)) == $designDir) {
            $theme = $this->_getThemeByFilename($filename);
            $group = $theme->getThemeId();
        } elseif (substr($filename, 0, strlen($jsDir)) == $jsDir) {
            $group = $jsDir;
        } elseif (substr($filename, 0, strlen($codeDir)) == $codeDir) {
            $group = $codeDir;
        } else {
            Mage::throwException($this->__('Invalid view file directory "%s"', $filename));
        }

        return array($group, $theme);
    }

    /**
     * Sort themes according to their hierarchy
     *
     * @param array $themes
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected function _sortThemes(&$themes)
    {
        uasort($themes, function($theme, $theme2) {
            /** @var $theme Mage_Core_Model_Theme */
            /** @var $theme2 Mage_Core_Model_Theme */
            while ($parentTheme = $theme->getParentTheme()) {
                if ($parentTheme->getId() == $theme2->getId()) {
                    return -1;
                }
            }
            return 1;
        });

        return $this;
    }

    /**
     * Get theme object that contains gien file
     *
     * @param string $filename
     * @return Mage_Core_Model_Theme
     */
    protected function _getThemeByFilename($filename)
    {
        $designDir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        list(, $area, $package, $theme,) = explode('/', substr($filename, strlen($designDir)), 5);
        /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
        $collection = Mage::getModel('Mage_Core_Model_Resource_Theme_Collection');
        return $collection->getThemeByFullPath(join('/', array($area, $package, $theme)));
    }

    /**
     * Get group labels
     *
     * @param array $themes
     * @return array
     */
    protected function _getGroupLabels($themes)
    {
        $labels = array(
            $this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB)   => $this->__('Library files'),
            $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES)   => $this->__('Framework files')
        );
        foreach ($themes as $theme) {
            /** @var $theme Mage_Core_Model_Theme */
            $labels[$theme->getThemeId()] = $this->__('"%s" Theme files', $theme->getThemeTitle());
        }

        return $labels;
    }

    /**
     * Sort one associative array according to another array
     *
     * $groups = array(
     *     b => item2,
     *     a => item1,
     *     c => item3,
     * );
     * $order = array(a,b,c);
     * result: array(
     *     a => item1,
     *     b => item2,
     *     c => item3,
     * )
     *
     * @param array $groups
     * @param array $order
     * @return array
     */
    protected function _sortArrayByArray($groups, $order)
    {
        $ordered = array();
        foreach ($order as $key) {
            if (array_key_exists($key, $groups)) {
                $ordered[$key] = $groups[$key];
                unset($groups[$key]);
            }
        }
        return $ordered + $groups;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('CSS Editor');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->_getCurrentTheme()->isVirtual() && $this->_getCurrentTheme()->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
