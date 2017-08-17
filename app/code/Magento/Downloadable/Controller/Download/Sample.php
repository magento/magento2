<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Download;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Downloadable\Controller\Download\Sample
 *
 */
class Sample extends \Magento\Downloadable\Controller\Download
{
    /**
     * Download sample action
     *
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('sample_id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_objectManager->create(\Magento\Downloadable\Model\Sample::class)->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                /** @var \Magento\Downloadable\Helper\File $helper */
                $helper = $this->_objectManager->get(\Magento\Downloadable\Helper\File::class);
                $resource = $helper->getFilePath($sample->getBasePath(), $sample->getSampleFile());
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Sorry, there was an error getting requested content. Please contact the store owner.')
                );
            }
        }
        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
