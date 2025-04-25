<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Bulk Delete selected catalog price rules
 */
class MassDelete extends MassAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param Context $context
     * @param CatalogRuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        Context $context,
        CatalogRuleRepositoryInterface $ruleRepository
    ) {
        $this->ruleRepository = $ruleRepository;
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
        $ids = $this->getRequest()->getParam('catalogpricerule');
        if ($ids) {
            foreach ($ids as $id) {
                $this->ruleRepository->deleteById($id);
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', count($ids)));
        } else {
            $this->messageManager->addErrorMessage(__('Please select a catalog price rule(s)'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('catalog_rule/*/');
    }
}
