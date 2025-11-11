<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Bulk Delete selected catalog price rules
 */
class MassDelete extends AbstractAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @param Context $context
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private TaxRateRepositoryInterface $taxRateRepository,
        private LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Delete selected catalog price rules
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('tax_rate_ids');
        $taxRateDeleted = 0;
        $taxRateDeleteError = 0;
        foreach ($ids as $rateId) {
            try {
                $this->taxRateRepository->deleteById((int)$rateId);
                $taxRateDeleted++;
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $taxRateDeleteError++;
            }
        }

        if ($taxRateDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $taxRateDeleted)
            );
        }

        if ($taxRateDeleteError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $taxRateDeleteError
                )
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('tax/*/');
    }
}
