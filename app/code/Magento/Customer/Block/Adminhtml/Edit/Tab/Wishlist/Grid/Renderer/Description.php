<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Wishlist\Grid\Renderer;

use \Magento\Backend\Block\Context;

/**
 * Adminhtml customers wishlist grid item renderer for item visibility
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     * @param \Magento\Framework\Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        array $data = [],
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Escaper::class
        );
        parent::__construct($context, $data);
    }

    /**
     * Render the description of given row.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return nl2br($this->escaper->escapeHtml($row->getData($this->getColumn()->getIndex())));
    }
}
