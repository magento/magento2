<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Grid\Renderer;

/**
 * Class \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action
 *
 * @since 2.0.0
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var Action\UrlBuilder
     * @since 2.0.0
     */
    protected $actionUrlBuilder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Action\UrlBuilder $actionUrlBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Action\UrlBuilder $actionUrlBuilder,
        array $data = []
    ) {
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $href = $this->actionUrlBuilder->getUrl(
            $row->getIdentifier(),
            $row->getData('_first_store_id'),
            $row->getStoreCode()
        );
        return '<a href="' . $href . '" target="_blank">' . __('Preview') . '</a>';
    }
}
