<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Product\Compare;

class Index extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Compare index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $items = $this->getRequest()->getParam('items');

        $beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);
        if ($beforeUrl) {
            $this->_catalogSession->setBeforeCompareUrl(
                $this->_objectManager->get('Magento\Core\Helper\Data')->urlDecode($beforeUrl)
            );
        }

        if ($items) {
            $items = explode(',', $items);
            /** @var \Magento\Catalog\Model\Product\Compare\ListCompare $list */
            $list = $this->_catalogProductCompareList;
            $list->addProducts($items);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/*');
        }
        return $this->resultPageFactory->create();
    }
}
