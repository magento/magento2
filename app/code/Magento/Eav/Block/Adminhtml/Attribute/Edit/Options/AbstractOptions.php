<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit\Options;

/**
 * Attribute add/edit form options tab
 *
 * @api
 * @deprecated 2.2.0
 */
abstract class AbstractOptions extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Preparing layout, adding buttons
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('labels', \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Labels::class);
        $this->addChild('options', \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options::class);
        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getChildHtml();
    }
}
