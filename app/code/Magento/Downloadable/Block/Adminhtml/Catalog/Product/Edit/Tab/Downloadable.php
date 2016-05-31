<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 */
class Downloadable extends Widget implements TabInterface
{
    /**
     * Reference to product objects that is being edited
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $_config = null;

    /**
     * @var string
     */
    protected $_template = 'product/edit/downloadable.phtml';

    /**
     * Accordion block id
     *
     * @var string
     */
    protected $blockId = 'downloadableInfo';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
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
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * Check is readonly block
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getDownloadableReadonly();
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Downloadable Information');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Downloadable Information');
    }

    /**
     * Check if tab can be displayed
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getGroupCode()
    {
        return Tabs::ADVANCED_TAB_GROUP_CODE;
    }

    /**
     * Get downloadable tab content id
     *
     * @return string
     */
    public function getContentTabId()
    {
        return 'tab_content_' . $this->blockId;
    }

    /**
     * @return bool
     */
    public function isDownloadable()
    {
        return $this->getProduct()->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', $this->isDownloadable());
        return parent::_prepareLayout();
    }
}
