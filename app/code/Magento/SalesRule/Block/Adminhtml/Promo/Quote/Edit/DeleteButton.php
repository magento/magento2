<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Escaper;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Escaper for secure output rendering
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $registry);
    }

    /**
     * Get delete button configuration data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId && $this->canRender('delete')) {
            $confirmMessage = $this->escaper->escapeJs(
                $this->escaper->escapeHtml(__('Are you sure you want to delete this?'))
            );
            $deleteUrl = $this->urlBuilder->getUrl('*/*/delete', ['id' => $ruleId]);
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . $confirmMessage . '\', \'' . $deleteUrl . '\', {data: {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
