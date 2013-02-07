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
 * Files content block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Content extends Mage_Backend_Block_Widget_Container
{
    /**
     * @var Mage_Theme_Helper_Storage
     */
    protected $_helperStorage;

    /**
     * Initialize dependencies
     *
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
     * @param Mage_Theme_Helper_Storage $helperStorage
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
        Mage_Theme_Helper_Storage $helperStorage,
        array $data = array()
    ) {
        $this->_helperStorage = $helperStorage;
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data);
    }

    /**
     * Block construction
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_headerText = $this->__('Media Storage');
        $this->_removeButton('back')->_removeButton('edit');
        $this->_addButton('newfolder', array(
            'class'   => 'save',
            'label'   => $this->__('Create Folder...'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.newFolder();'
        ));

        $this->_addButton('delete_folder', array(
            'class'   => 'delete no-display',
            'label'   => $this->__('Delete Folder'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.deleteFolder();',
            'id'      => 'button_delete_folder'
        ));

        $this->_addButton('delete_files', array(
            'class'   => 'delete no-display',
            'label'   => $this->__('Delete File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.deleteFiles();',
            'id'      => 'button_delete_files'
        ));

        $this->_addButton('insert_files', array(
            'class'   => 'save no-display',
            'label'   => $this->__('Insert File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.insert();',
            'id'      => 'button_insert_files'
        ));
    }

    /**
     * Files action source URL
     *
     * @return string
     */
    public function getContentsUrl()
    {
        return $this->getUrl('*/*/contents', array('type' => $this->getRequest()->getParam('type'))
            + $this->_helperStorage->getRequestParams());
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new Varien_Object();

        $setupObject->setData(array(
            'newFolderPrompt'                 => $this->__('New Folder Name:'),
            'deleteFolderConfirmationMessage' => $this->__('Are you sure you want to delete current folder?'),
            'deleteFileConfirmationMessage'   => $this->__('Are you sure you want to delete the selected file?'),
            'targetElementId' => $this->getTargetElementId(),
            'contentsUrl'     => $this->getContentsUrl(),
            'onInsertUrl'     => $this->getOnInsertUrl(),
            'newFolderUrl'    => $this->getNewfolderUrl(),
            'deleteFolderUrl' => $this->getDeletefolderUrl(),
            'deleteFilesUrl'  => $this->getDeleteFilesUrl(),
            'headerText'      => $this->getHeaderText()
        ));

        return $this->helper('Mage_Core_Helper_Data')->jsonEncode($setupObject);
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getNewfolderUrl()
    {
        return $this->getUrl('*/*/newFolder', $this->_helperStorage->getRequestParams());
    }

    /**
     * Delete directory action target URL
     *
     * @return string
     */
    protected function getDeletefolderUrl()
    {
        return $this->getUrl('*/*/deleteFolder', $this->_helperStorage->getRequestParams());
    }

    /**
     * Delete files action target URL
     *
     * @return string
     */
    public function getDeleteFilesUrl()
    {
        return $this->getUrl('*/*/deleteFiles', $this->_helperStorage->getRequestParams());
    }

    /**
     * Insert file action target URL
     *
     * @return string
     */
    public function getOnInsertUrl()
    {
        return $this->getUrl('*/*/onInsert', $this->_helperStorage->getRequestParams());
    }

    /**
     * Target element ID getter
     *
     * @return string
     */
    public function getTargetElementId()
    {
        return $this->getRequest()->getParam('target_element_id');
    }
}
