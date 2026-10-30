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

class MassActivate extends MassAction implements HttpPostActionInterface, HttpGetActionInterface
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
     * Bulk activate catalog price rule
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('catalogpricerule');
        try {
            if ($ids) {
                foreach ($ids as $id) {
                    $model = $this->ruleRepository->get($id);
                    $model->setIsActive(1);
                    $this->ruleRepository->save($model);
                }
                $this->messageManager->addSuccessMessage(__('You activated a total of %1 records.', count($ids)));
            } else {
                $this->messageManager->addErrorMessage(__('Please select a catalog price rule(s)'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog_rule/*/');
    }
}
