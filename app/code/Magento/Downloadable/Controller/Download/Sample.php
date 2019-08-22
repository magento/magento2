<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Controller\Download;

use Magento\Catalog\Model\Product\SalabilityChecker;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Sample executes download sample action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Sample extends \Magento\Downloadable\Controller\Download
{
    /**
     * @var SalabilityChecker
     */
    private $salabilityChecker;

    /**
     * @param Context $context
     * @param SalabilityChecker|null $salabilityChecker
     */
    public function __construct(
        Context $context,
        SalabilityChecker $salabilityChecker = null
    ) {
        parent::__construct($context);
        $this->salabilityChecker = $salabilityChecker ?: $this->_objectManager->get(SalabilityChecker::class);
    }

    /**
     * Download sample action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('sample_id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_objectManager->create(\Magento\Downloadable\Model\Sample::class)->load($sampleId);
        if ($sample->getId() && $this->salabilityChecker->isSalable($sample->getProductId())) {
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
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
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
