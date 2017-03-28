<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Attribute add/edit form options tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit\Options;

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
