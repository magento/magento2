<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 * @package Magento\Cms\Controller\Adminhtml\Block
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::block';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory = null,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository = null
    ) {
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->blockFactory = $blockFactory
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Model\BlockFactory::class);
        $this->blockRepository = $blockRepository
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Api\BlockRepositoryInterface::class);
        parent::__construct($context);
    }

    /**
     * Edit CMS block
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $blockId = $this->getRequest()->getParam('block_id');
        $model = $this->prepareModel($blockId);

        if (!$model || ($blockId && $model->getId() != $blockId)) {
            $this->messageManager->addErrorMessage(__('This block no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register('cms_block', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initAction($resultPage);
        $resultPage->addBreadcrumb(
            $blockId ? __('Edit Block') : __('New Block'),
            $blockId ? __('Edit Block') : __('New Block')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Blocks'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Block'));

        return $resultPage;
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    private function initAction($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Cms::cms_block')
            ->addBreadcrumb(__('CMS'), __('CMS'))
            ->addBreadcrumb(__('Static Blocks'), __('Static Blocks'));
    }

    /**
     * Retrieve and prepare model.
     *
     * @param $blockId
     * @return bool|\Magento\Cms\Api\Data\PageInterface
     */
    private function prepareModel($blockId)
    {
        if ($blockId) {
            try {
                $model = $this->blockRepository->getById($blockId);
            } catch (NoSuchEntityException $e) {
                $model = false;
            }
        }

        if (!isset($model)) {
            $model = $this->blockFactory->create();
        }

        return $model;
    }
}
