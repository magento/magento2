<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerIdProvider;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class ShoppingCarts
 *
 * @package Magento\Sales\Block\Adminhtml
 */
class ShoppingCartsTab extends TabWrapper
{
    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * @var CustomerIdProvider
     */
    private $customerIdProvider;

    /**
     * @param Context $context
     * @param CustomerIdProvider $customerIdProvider
     * @param array $data
     */
    public function __construct(Context $context, CustomerIdProvider $customerIdProvider, array $data = [])
    {
        parent::__construct($context, $data);
        $this->customerIdProvider = $customerIdProvider;
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->customerIdProvider->getCustomerId();
    }

    /**
     * Return Tab label
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Shopping cart');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('customer/*/cart', ['_current' => true]);
    }
}
