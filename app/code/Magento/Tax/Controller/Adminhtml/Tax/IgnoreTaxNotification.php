<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Tax;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Controller\Adminhtml\Tax;

class IgnoreTaxNotification extends Tax
{
    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param Context $context
     * @param TaxClassRepositoryInterface $taxClassService
     * @param TaxClassInterfaceFactory $taxClassDataObjectFactory
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        TaxClassRepositoryInterface $taxClassService,
        TaxClassInterfaceFactory $taxClassDataObjectFactory,
        TypeListInterface $cacheTypeList
    ) {
        $this->_cacheTypeList = $cacheTypeList;
        parent::__construct($context, $taxClassService, $taxClassDataObjectFactory);
    }

    /**
     * Set tax ignore notification flag and redirect back
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        $section = $this->getRequest()->getParam('section');
        if ($section) {
            try {
                $path = 'tax/notification/ignore_' . $section;
                $this->_objectManager->get(Config::class)
                    ->saveConfig($path, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        // clear the block html cache
        $this->_cacheTypeList->cleanType('config');
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setRefererUrl();
    }
}
