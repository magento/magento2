<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Plugin\Helper\Product;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Page;

/**
 * Parses the query string and pre-fills product quantity
 */
class View
{
    /**
     * Parses the query string and pre-fills product quantity
     *
     * @param \Magento\Catalog\Helper\Product\View $view
     * @param Page $resultPage
     * @param mixed $productId
     * @param AbstractAction $controller
     * @param mixed $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareAndRender(
        \Magento\Catalog\Helper\Product\View $view,
        Page $resultPage,
        $productId,
        AbstractAction $controller,
        $params = null
    ) {
        $qty = $controller->getRequest()->getParam('qty');
        if ($qty) {
            if (null === $params || !$params instanceof DataObject) {
                $params = new DataObject((array) $params);
            }
            if (!$params->getBuyRequest()) {
                $params->setBuyRequest(new DataObject([]));
            }
            $params->getBuyRequest()->setQty($qty);
        }

        return [$resultPage, $productId, $controller, $params];
    }
}
