<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Country grid filter
 * @since 2.0.0
 */
class Country extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.0.0
     */
    protected $_directoriesFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $directoriesFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $directoriesFactory,
        array $data = []
    ) {
        $this->_directoriesFactory = $directoriesFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return array
     * @since 2.0.0
     */
    protected function _getOptions()
    {
        $options = $this->_directoriesFactory->create()->load()->toOptionArray(false);
        array_unshift($options, ['value' => '', 'label' => __('All Countries')]);
        return $options;
    }
}
