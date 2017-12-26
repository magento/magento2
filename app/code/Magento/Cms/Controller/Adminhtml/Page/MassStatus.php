<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassStatus
 * @package Magento\Cms\Controller\Adminhtml\Page
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::save';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->pageRepository = $pageRepository
            ?: ObjectManager::getInstance()->get(\Magento\Cms\Api\PageRepositoryInterface::class);
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {
            $status = $this->getRequest()->getParam('status');
            if ($status === null) {
                throw new LocalizedException(__('Status is a required param.'));
            }
            $status = (bool) $status;
            $statusLabel = $status ? __('Enabled') : __('Disabled');
            foreach ($collection as $item) {
                $item->setIsActive($status);
                $this->pageRepository->save($item);
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have changed their status to %2.', $collection->getSize(), $statusLabel)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while changing the page status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
