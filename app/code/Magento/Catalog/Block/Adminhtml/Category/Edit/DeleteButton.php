<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Framework\Escaper;

class DeleteButton extends AbstractCategory implements ButtonProviderInterface
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }

    /**
     * Delete button
     *
     * @return array
     */
    public function getButtonData()
    {
        $category = $this->getCategory();
        $categoryId = (int)$category->getId();

        if ($categoryId && !in_array($categoryId, $this->getRootIds()) && $category->isDeleteable()) {
            $confirmMessage = $this->escaper->escapeJs(
                $this->escaper->escapeHtml(__('Are you sure you want to delete this category?'))
            );
            return [
                'id' => 'delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('" . $confirmMessage . "', '" . $this->getDeleteUrl() . "', {data: {}})",
                'class' => 'delete',
                'sort_order' => 10
            ];
        }

        return [];
    }

    /**
     * Get the delete URL for category
     *
     * @param array $args
     * @return string
     */
    public function getDeleteUrl(array $args = [])
    {
        $params = array_merge($this->getDefaultUrlParams(), $args);
        return $this->getUrl('catalog/*/delete', $params);
    }

    /**
     * Get default URL parameters
     *
     * @return array
     */
    protected function getDefaultUrlParams()
    {
        return ['_current' => true, '_query' => ['isAjax' => null]];
    }
}
