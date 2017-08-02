<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Adminhtml;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class CartTab
 *
 * @package Magento\Checkout\Block\Adminhtml
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class CartTab extends TabWrapper
{
    /**
     * Core registry
     *
     * @var Registry
     * @since 2.0.0
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Shopping Cart');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return $this->getUrl('customer/*/carts', ['_current' => true]);
    }
}
