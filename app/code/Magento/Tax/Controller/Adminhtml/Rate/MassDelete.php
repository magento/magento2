<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Controller\Adminhtml\Rate;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Tax Rates Mass Delete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @var TaxRateRepositoryInterface
     */
    private $taxRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        TaxRateRepositoryInterface $taxRateRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->taxRepository = $taxRateRepository;
        $this->logger = $logger;
    }

    /**
     * Mass Delete Action
     *
     * @return Redirect
     */
    public function execute()
    {
        $taxRateDeleted = 0;
        $taxRateDeleteError = 0;
        foreach ($this->getTaxRatesIds() as $rateId) {
            try {
                $this->taxRepository->deleteById((int)$rateId);
                $taxRateDeleted++;
            } catch (Exception $e) {
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

        return $this->resultRedirectFactory->create()->setPath('tax/*/index');
    }

    /**
     * Retrieves tax rates IDs from request
     *
     * @return array
     */
    private function getTaxRatesIds(): array
    {
        $taxRatesIds = $this->getRequest()->getParam('tax_rate_ids', []);

        $taxRatesIds = is_array($taxRatesIds) ? $taxRatesIds : explode(',', $taxRatesIds);

        return array_unique($taxRatesIds);
    }
}
