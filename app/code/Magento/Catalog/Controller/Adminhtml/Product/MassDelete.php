<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Model\Resource\Product\Collection;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Field id
     */
    const ID_FIELD = 'entity_id';

    /**
     * Redirect url
     */
    const REDIRECT_URL = 'catalog/*/index';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Catalog\Model\Resource\Product\Collection';

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
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
                $this->messageManager->addError(__('Please select product(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
    }

    /**
     * Cancel selected orders
     *
     * @param Collection $collection
     * @return void
     */
    protected function massAction($collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $product) {
            $product->delete();
            ++$count;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
    }
}
