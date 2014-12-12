<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $this->addChild('labels', 'Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Labels');
        $this->addChild('options', 'Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options');
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
