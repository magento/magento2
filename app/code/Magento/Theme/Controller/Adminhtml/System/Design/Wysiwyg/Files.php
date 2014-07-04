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

/**
 * Files controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg;

use Magento\Framework\App\ResponseInterface;

class Files extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $storage;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Theme\Helper\Storage $storage
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Theme\Helper\Storage $storage
    ) {
        $this->_fileFactory = $fileFactory;
        $this->storage = $storage;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_view->loadLayout('overlay_popup');
        $this->_view->renderLayout();
    }

    /**
     * Tree json action
     *
     * @return void
     */
    public function treeJsonAction()
    {
        try {
            $this->getResponse()->representJson(
                $this->_view->getLayout()->createBlock(
                    'Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree'
                )->getTreeJson(
                    $this->_getStorage()->getTreeArray()
                )
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array())
            );
        }
    }

    /**
     * New folder action
     *
     * @return void
     */
    public function newFolderAction()
    {
        $name = $this->getRequest()->getPost('name');
        try {
            $path = $this->storage->getCurrentPath();
            $result = $this->_getStorage()->createFolder($name, $path);
        } catch (\Magento\Framework\Model\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('Sorry, there was an unknown error.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Delete folder action
     *
     * @return void
     */
    public function deleteFolderAction()
    {
        try {
            $path = $this->storage->getCurrentPath();
            $this->_getStorage()->deleteDirectory($path);
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Contents action
     *
     * @return void
     */
    public function contentsAction()
    {
        try {
            $this->_view->loadLayout('empty');
            $this->_view->getLayout()->getBlock('wysiwyg_files.files')->setStorage($this->_getStorage());
            $this->_view->renderLayout();

            $this->_getSession()->setStoragePath($this->storage->getCurrentPath());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Files upload action
     *
     * @return void
     */
    public function uploadAction()
    {
        try {
            $path = $this->storage->getCurrentPath();
            $result = $this->_getStorage()->uploadFile($path);
        } catch (\Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Preview image action
     *
     * @return ResponseInterface|void
     */
    public function previewImageAction()
    {
        $file = $this->getRequest()->getParam('file');
        /** @var $helper \Magento\Theme\Helper\Storage */
        $helper = $this->_objectManager->get('Magento\Theme\Helper\Storage');
        try {
            return $this->_fileFactory->create(
                $file,
                array('type' => 'filename', 'value' => $helper->getThumbnailPath($file)),
                \Magento\Framework\App\Filesystem::MEDIA_DIR
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_redirect('core/index/notfound');
        }
    }

    /**
     * Delete file from media storage
     *
     * @return void
     * @throws \Exception
     */
    public function deleteFilesAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('Wrong request');
            }
            $files = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonDecode(
                $this->getRequest()->getParam('files')
            );
            foreach ($files as $file) {
                $this->_getStorage()->deleteFile($file);
            }
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Fire when select image
     *
     * @return void
     */
    public function onInsertAction()
    {
        /** @var $helperStorage \Magento\Theme\Helper\Storage */
        $helperStorage = $this->_objectManager->get('Magento\Theme\Helper\Storage');
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }

    /**
     * Get storage
     *
     * @return \Magento\Theme\Model\Wysiwyg\Storage
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get('Magento\Theme\Model\Wysiwyg\Storage');
    }
}
