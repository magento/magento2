<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Block\Onepage\Shipping;

/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Method extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->getCheckout()->setStepData(
            'shipping_method',
            ['label' => __('Shipping Method'), 'is_show' => $this->isShow()]
        );
        parent::_construct();
    }

    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }
}
