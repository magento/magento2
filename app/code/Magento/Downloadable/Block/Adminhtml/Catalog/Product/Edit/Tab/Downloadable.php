<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs;
use Magento\Framework\Registry;

/**
 * Adminhtml catalog product downloadable items tab and form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Downloadable extends Widget implements TabInterface
{
    /**
     * Reference to product objects that is being edited
     *
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\DataObject|null
     * @since 2.0.0
     */
    protected $_config = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/edit/downloadable.phtml';

    /**
     * Accordion block id
     *
     * @var string
     * @since 2.0.0
     */
    protected $blockId = 'downloadableInfo';

    /**
     * Core registry
     *
     * @var Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get parent tab code
     *
     * @return string
     * @since 2.0.0
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Check is readonly block
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isReadonly()
    {
        return $this->getProduct()->getDownloadableReadonly();
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Downloadable Information');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Downloadable Information');
    }

    /**
     * Check if tab can be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getGroupCode()
    {
        return Tabs::ADVANCED_TAB_GROUP_CODE;
    }

    /**
     * Get downloadable tab content id
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentTabId()
    {
        return 'tab_content_' . $this->blockId;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isDownloadable()
    {
        return $this->getProduct()->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', $this->isDownloadable());
        return parent::_prepareLayout();
    }
}
