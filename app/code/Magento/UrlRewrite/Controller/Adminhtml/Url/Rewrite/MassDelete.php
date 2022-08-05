<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory as CollectionFactory;

/**
 * Mass delete action class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDelete extends Rewrite implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Mass delete action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $notProcessedCount = $processedCount = 0;
        try {
            foreach ($collection as $urlRewrite) {
                $urlRewrite->delete();
                $processedCount++;
            }
        } catch (Exception $e) {
            $this->getMessageManager()->addExceptionMessage($e);
            $notProcessedCount++;
        }

        $this->getMessageManager()->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $processedCount)
        );

        if ($notProcessedCount > 0) {
            $this->getMessageManager()->addErrorMessage(
                __('A total of %1 record(s) haven\'t been deleted.', $notProcessedCount)
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
