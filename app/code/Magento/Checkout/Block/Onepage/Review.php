<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Block\Onepage;

class Review extends AbstractOnepage
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'review',
            ['label' => __('Order Review'), 'is_show' => $this->isShow()]
        );
        parent::_construct();
    }
}
