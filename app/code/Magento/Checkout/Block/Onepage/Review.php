<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
