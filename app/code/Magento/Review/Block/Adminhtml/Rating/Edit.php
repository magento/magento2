<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating;

/**
 * Rating edit form
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Rating factory
     *
     * @var \Magento\Review\Model\RatingFactory
     * @since 2.0.0
     */
    protected $_ratingFactory;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_blockGroup = 'Magento_Review';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_ratingFactory = $ratingFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rating';
        $this->_blockGroup = 'Magento_Review';

        $this->buttonList->update('save', 'label', __('Save Rating'));
        $this->buttonList->update('delete', 'label', __('Delete Rating'));

        if ($this->getRequest()->getParam($this->_objectId)) {
            $ratingData = $this->_ratingFactory->create()->load($this->getRequest()->getParam($this->_objectId));

            $this->_coreRegistry->register('rating_data', $ratingData);
        }
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        $ratingData = $this->_coreRegistry->registry('rating_data');
        if ($ratingData && $ratingData->getId()) {
            return __("Edit Rating #%1", $this->escapeHtml($ratingData->getRatingCode()));
        } else {
            return __('New Rating');
        }
    }
}
