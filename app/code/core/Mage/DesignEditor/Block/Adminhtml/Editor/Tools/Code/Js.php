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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block that renders JS tab
 *
 * @method Mage_Core_Model_Theme getTheme()
 * @method setTheme($theme)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js extends Mage_Backend_Block_Widget_Form
{
    /**
     * @var Mage_Core_Model_Theme_Service
     */
    protected $_service;

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
     * @param Mage_Core_Model_Theme_Service $service
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
        Mage_Core_Model_Theme_Service $service,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data
        );
        $this->_service = $service;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'action' => '#',
            'method' => 'post'
        ));
        $this->setForm($form);
        $form->setUseContainer(true);

        $form->addType('js_files', 'Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_File');

        $jsConfig = array(
            'name'     => 'js_files_uploader',
            'title'    => $this->__('Select JS Files to Upload'),
            'accept'   => 'application/x-javascript',
            'multiple' => '',
        );
        if ($this->_service->isThemeAssignedToStore($this->getTheme())) {
            $confirmMessage = $this->__('You are about to upload JavaScript files. '
                . 'This will take effect immediately and might affect the design of your store if your theme '
                . 'is assigned to the store front. Are you sure you want to do this?');
            $jsConfig['onclick'] = "return confirm('{$confirmMessage}');";
        }
        $form->addField('js_files_uploader', 'js_files', $jsConfig);

        parent::_prepareForm();
        return $this;
    }

    /**
     * Return confirmation message for delete action
     *
     * @return string
     */
    public function getConfirmMessageDelete()
    {
        if ($this->_service->isThemeAssignedToStore($this->getTheme())) {
            $confirmMessage = $this->__('Are you sure you want to delete the selected JavaScript file? This operation'
                . ' cannot be undone. It will affect the theme and frontend design if the theme is currently assigned'
                . '  to the store front');
        } else {
            $confirmMessage = $this->__('Are you sure you want to delete the selected JavaScript file? This operation'
                . 'cannot be undone. It will affect the theme.');
        }
        return $confirmMessage;
    }

    /**
     * Get upload js url
     *
     * @return string
     */
    public function getJsUploadUrl()
    {
        return $this->getUrl('*/system_design_editor_tools/uploadjs', array('id' => $this->getTheme()->getId()));
    }

    /**
     * Get reorder js url
     *
     * @return string
     */
    public function getJsReorderUrl()
    {
        return $this->getUrl('*/system_design_editor_tools/reorderjs', array('id' => $this->getTheme()->getId()));
    }

    /**
     * Get delete js url
     *
     * @return string
     */
    public function getJsDeleteUrl()
    {
        return $this->getUrl('*/system_design_editor_tools/deleteCustomFiles', array(
            'id' => $this->getTheme()->getId()
        ));
    }

    /**
     * Get custom js files
     *
     * @return Mage_Core_Model_Resource_Theme_Files_Collection
     */
    public function getJsFiles()
    {
        return $this->getTheme()->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Js::TYPE);
    }

    /**
     * Get js tab title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__('Custom javascript files');
    }
}
