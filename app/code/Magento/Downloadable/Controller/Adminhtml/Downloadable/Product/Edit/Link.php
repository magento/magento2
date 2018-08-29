<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Response\Http as HttpResponse;

class Link extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _createLink()
    {
        return $this->_objectManager->create(\Magento\Downloadable\Model\Link::class);
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->_objectManager->get(\Magento\Downloadable\Model\Link::class);
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     * @return void
     */
    protected function _processDownload($resource, $resourceType)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->_objectManager->get(\Magento\Downloadable\Helper\Download::class);
        $helper->setResource($resource, $resourceType);

        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $response->setHeader('Content-Length', $fileSize);
        }
        //Setting disposition as state in the config or forcing it for HTML.
        /** @var string|null $contentDisposition */
        $contentDisposition = $helper->getContentDisposition();
        if (!$contentDisposition || $contentType === 'text/html') {
            $contentDisposition = 'attachment';
        }
        $response->setHeader(
            'Content-Disposition',
            $contentDisposition . '; filename=' . $fileName
        );
        //Rendering
        $response->clearBody();
        $response->sendHeaders();
        
        $helper->output();
    }

    /**
     * Download link action
     *
     * @return void
     */
    public function execute()
    {
        $linkId = $this->getRequest()->getParam('id', 0);
        $type = $this->getRequest()->getParam('type', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_createLink()->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($type == 'link') {
                if ($link->getLinkType() == DownloadHelper::LINK_TYPE_URL) {
                    $resource = $link->getLinkUrl();
                    $resourceType = DownloadHelper::LINK_TYPE_URL;
                } elseif ($link->getLinkType() == DownloadHelper::LINK_TYPE_FILE) {
                    $resource = $this->_objectManager->get(
                        \Magento\Downloadable\Helper\File::class
                    )->getFilePath(
                        $this->_getLink()->getBasePath(),
                        $link->getLinkFile()
                    );
                    $resourceType = DownloadHelper::LINK_TYPE_FILE;
                }
            } else {
                if ($link->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                    $resource = $link->getSampleUrl();
                    $resourceType = DownloadHelper::LINK_TYPE_URL;
                } elseif ($link->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                    $resource = $this->_objectManager->get(
                        \Magento\Downloadable\Helper\File::class
                    )->getFilePath(
                        $this->_getLink()->getBaseSamplePath(),
                        $link->getSampleFile()
                    );
                    $resourceType = DownloadHelper::LINK_TYPE_FILE;
                }
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
