<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * Download controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var array
     */
    private $disallowedContentTypes = [
        'text/html',
    ];

    /**
     * Prepare response to output resource contents
     *
     * @param string $path         Path to resource
     * @param string $resourceType Type of resource (see Magento\Downloadable\Helper\Download::LINK_TYPE_* constants)
     * @return void
     */
    protected function _processDownload($path, $resourceType)
    {
        /* @var $helper DownloadHelper */
        $helper = $this->_objectManager->get(\Magento\Downloadable\Helper\Download::class);

        $helper->setResource($path, $resourceType);
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

        $contentDisposition = $helper->getContentDisposition();
        if (!$contentDisposition || in_array($contentType, $this->disallowedContentTypes)) {
            // For security reasons we force browsers to download the file instead of opening it.
            $contentDisposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        }

        $response->setHeader('Content-Disposition', $contentDisposition  . '; filename=' . $fileName);
        //Rendering
        $response->clearBody();
        $response->sendHeaders();

        $helper->output();
    }

    /**
     * Get link model
     *
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->_objectManager->get(\Magento\Downloadable\Model\Link::class);
    }
}
