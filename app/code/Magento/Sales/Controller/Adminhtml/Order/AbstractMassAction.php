<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractMassStatus
 */
abstract class AbstractMassAction extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'entity_id';

    /**
     * Redirect url
     */
    const REDIRECT_URL = 'sales/order/';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Sales\Model\Resource\Order\Grid\Collection';

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        $collection = $this->_objectManager->create($this->collection);
        try {
            if (!empty($excluded)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
                $this->massAction($collection);
            } elseif (!empty($selected)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
                $this->massAction($collection);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
    }

    /**
     * Set status to collection items
     *
     * @param AbstractCollection $collection
     * @return void
     */
    abstract protected function massAction(AbstractCollection $collection);
}
