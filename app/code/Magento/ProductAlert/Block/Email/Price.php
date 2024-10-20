<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Email;

/**
 * ProductAlert email price changed grid
 */
class Price extends \Magento\ProductAlert\Block\Email\AbstractEmail
{
    /**
     * @var string
     */
    protected $_template = 'Magento_ProductAlert::email/price.phtml';

    /**
     * Retrieve unsubscribe url for product
     *
     * @param int $productId
     * @return string
     */
    public function getProductUnsubscribeUrl($productId)
    {
        $params = $this->_getUrlParams();
        $params['product'] = $productId;
        return $this->getUrl('productalert/unsubscribe/price', $params);
    }

    /**
     * Retrieve unsubscribe url for all products
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return $this->getUrl('productalert/unsubscribe/priceAll', $this->_getUrlParams());
    }
}
