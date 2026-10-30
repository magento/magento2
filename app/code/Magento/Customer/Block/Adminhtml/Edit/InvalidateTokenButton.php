<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class InvalidateTokenButton for force sign button data
 */
class InvalidateTokenButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->escaper = $context->getEscaper();
    }

    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) {
            $deleteConfirmMsg = $this->escaper->escapeJs(
                $this->escaper->escapeHtml(__("Are you sure you want to revoke the customer's tokens?"))
            );
            $data = [
                'label' => __('Force Sign-In'),
                'class' => 'invalidate-token',
                'on_click' => 'deleteConfirm("' . $deleteConfirmMsg . '", "' . $this->getInvalidateTokenUrl() . '")',
                'sort_order' => 65,
                'aclResource' => 'Magento_Customer::invalidate_tokens',
            ];
        }
        return $data;
    }

    /**
     * Get invalidate token url.
     *
     * @return string
     */
    public function getInvalidateTokenUrl()
    {
        return $this->getUrl('customer/customer/invalidateToken', ['customer_id' => $this->getCustomerId()]);
    }
}
