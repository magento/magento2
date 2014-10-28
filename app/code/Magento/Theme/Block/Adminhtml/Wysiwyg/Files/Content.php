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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files;

/**
 * Files content block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Content extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_storageHelper;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Theme\Helper\Storage $storageHelper,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Block construction
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_headerText = __('Media Storage');
        $this->buttonList->remove('back');
        $this->buttonList->remove('edit');
        $this->buttonList->add(
            'newfolder',
            array(
                'class' => 'save',
                'label' => __('Create Folder'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.newFolder();'
            )
        );

        $this->buttonList->add(
            'delete_folder',
            array(
                'class' => 'delete no-display',
                'label' => __('Delete Folder'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.deleteFolder();',
                'id' => 'button_delete_folder'
            )
        );

        $this->buttonList->add(
            'delete_files',
            array(
                'class' => 'delete no-display',
                'label' => __('Delete File'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.deleteFiles();',
                'id' => 'button_delete_files'
            )
        );

        $this->buttonList->add(
            'insert_files',
            array(
                'class' => 'save no-display',
                'label' => __('Insert File'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.insert();',
                'id' => 'button_insert_files'
            )
        );
    }

    /**
     * Files action source URL
     *
     * @return string
     */
    public function getContentsUrl()
    {
        return $this->getUrl(
            'adminhtml/*/contents',
            array('type' => $this->getRequest()->getParam('type')) + $this->_storageHelper->getRequestParams()
        );
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new \Magento\Framework\Object();

        $setupObject->setData(
            array(
                'newFolderPrompt' => __('New Folder Name:'),
                'deleteFolderConfirmationMessage' => __('Are you sure you want to delete this folder?'),
                'deleteFileConfirmationMessage' => __('Are you sure you want to delete this file?'),
                'targetElementId' => $this->getTargetElementId(),
                'contentsUrl' => $this->getContentsUrl(),
                'onInsertUrl' => $this->getOnInsertUrl(),
                'newFolderUrl' => $this->getNewfolderUrl(),
                'deleteFolderUrl' => $this->getDeletefolderUrl(),
                'deleteFilesUrl' => $this->getDeleteFilesUrl(),
                'headerText' => $this->getHeaderText(),
                'showBreadcrumbs' => true
            )
        );

        return $this->_coreHelper->jsonEncode($setupObject);
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getNewfolderUrl()
    {
        return $this->getUrl('adminhtml/*/newFolder', $this->_storageHelper->getRequestParams());
    }

    /**
     * Delete directory action target URL
     *
     * @return string
     */
    protected function getDeletefolderUrl()
    {
        return $this->getUrl('adminhtml/*/deleteFolder', $this->_storageHelper->getRequestParams());
    }

    /**
     * Delete files action target URL
     *
     * @return string
     */
    public function getDeleteFilesUrl()
    {
        return $this->getUrl('adminhtml/*/deleteFiles', $this->_storageHelper->getRequestParams());
    }

    /**
     * Insert file action target URL
     *
     * @return string
     */
    public function getOnInsertUrl()
    {
        return $this->getUrl('adminhtml/*/onInsert', $this->_storageHelper->getRequestParams());
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
