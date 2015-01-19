<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Category\Attribute\Source;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Page extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Block collection factory
     *
     * @var \Magento\Cms\Model\Resource\Block\Grid\CollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Cms\Model\Resource\Block\Grid\CollectionFactory $blockCollectionFactory
     */
    public function __construct(\Magento\Cms\Model\Resource\Block\Grid\CollectionFactory $blockCollectionFactory)
    {
        $this->_blockCollectionFactory = $blockCollectionFactory;
    }

    /**
     * Return all block options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_blockCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a static block.')]);
        }
        return $this->_options;
    }
}
