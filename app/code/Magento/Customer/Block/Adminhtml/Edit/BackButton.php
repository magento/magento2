<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class for BackButton
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        parent::__construct($context, $registry);
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->context->getRequest()->getParam('review_id')) {
            return $this->getUrl(
                'review/product/edit',
                ['id' => $this->context->getRequest()->getParam('review_id')]
            );
        }
        return $this->getUrl('*/*/');
    }
}
