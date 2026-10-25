<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Search\Block\Adminhtml\Synonyms\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Escaper;

/**
 * Delete Synonyms Group Button Class
 */
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
     * Delete Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getGroupId()) {
            $confirmMessage = $this->escaper->escapeJs(
                $this->escaper->escapeHtml(__('Are you sure you want to delete this synonym group?'))
            );
            $deleteOnClick = 'deleteConfirm(\'' . $confirmMessage . '\', \'' .
                $this->getDeleteUrl() . '\', {data: {}})';
            $data = [
                'label' => __('Delete Synonym Group'),
                'class' => 'delete',
                'on_click' => $deleteOnClick,
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Delete Url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['group_id' => $this->getGroupId()]);
    }
}
