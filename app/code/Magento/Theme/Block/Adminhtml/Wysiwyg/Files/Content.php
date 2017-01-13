<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Theme\Helper\Storage $storageHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
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
            [
                'class' => 'save',
                'label' => __('Create Folder'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.newFolder();'
            ]
        );

        $this->buttonList->add(
            'delete_folder',
            [
                'class' => 'delete no-display',
                'label' => __('Delete Folder'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.deleteFolder();',
                'id' => 'button_delete_folder'
            ]
        );

        $this->buttonList->add(
            'delete_files',
            [
                'class' => 'delete no-display',
                'label' => __('Delete File'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.deleteFiles();',
                'id' => 'button_delete_files'
            ]
        );

        $this->buttonList->add(
            'insert_files',
            [
                'class' => 'save no-display',
                'label' => __('Insert File'),
                'type' => 'button',
                'onclick' => 'MediabrowserInstance.insert();',
                'id' => 'button_insert_files'
            ]
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
            ['type' => $this->getRequest()->getParam('type')] + $this->_storageHelper->getRequestParams()
        );
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new \Magento\Framework\DataObject();

        $setupObject->setData(
            [
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
                'showBreadcrumbs' => true,
            ]
        );

        return $this->jsonHelper->jsonEncode($setupObject);
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
