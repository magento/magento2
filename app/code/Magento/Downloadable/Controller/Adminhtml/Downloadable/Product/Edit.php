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
 * Adminhtml downloadable product edit
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product;

use Magento\Downloadable\Helper\Download as DownloadHelper;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Load downloadable tab fieldsets
     *
     */
    public function formAction()
    {
        $this->_initProduct();
        $this->getResponse()->setBody(
            $this->_view->getLayout()
                ->createBlock(
                     'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable',
                    'admin.product.downloadable.information')
                ->toHtml()
        );
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     */
    protected function _processDownload($resource, $resourceType)
    {
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');
        /* @var $helper DownloadHelper */

        $helper->setResource($resource, $resourceType);

        $fileName       = $helper->getFilename();
        $contentType    = $helper->getContentType();

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()
                ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName);
        }

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->sendHeaders();

        $helper->output();
    }

    /**
     * Download link action
     *
     */
    public function linkAction()
    {
        $linkId = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_createLink()->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($link->getLinkType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $link->getLinkUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($link->getLinkType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get('Magento\Downloadable\Helper\File')->getFilePath(
                    $this->_getLink()->getBasePath(),
                    $link->getLinkFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
        exit(0);
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->_objectManager->get('Magento\Downloadable\Model\Link');
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _createLink()
    {
        return $this->_objectManager->create('Magento\Downloadable\Model\Link');
    }
}
