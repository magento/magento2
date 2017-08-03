<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class AbstractMassStatus
 * @deprecated 2.2.0
 * Never extend from this action. Implement mass-action logic in the "execute" method of your controller.
 * @since 2.0.0
 */
abstract class AbstractMassAction extends \Magento\Backend\App\Action
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $redirectUrl = '*/*/';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     * @since 2.0.0
     */
    protected $filter;

    /**
     * @var object
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @since 2.0.0
     */
    public function __construct(Context $context, Filter $filter)
    {
        parent::__construct($context);
        $this->filter = $filter;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Return component referrer url
     * TODO: Technical dept referrer url should be implement as a part of Action configuration in in appropriate way
     *
     * @return null|string
     * @since 2.0.0
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: 'sales/*/';
    }

    /**
     * Set status to collection items
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     * @since 2.0.0
     */
    abstract protected function massAction(AbstractCollection $collection);
}
