<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend form widget
 *
 */
namespace Magento\Backend\Block\Widget\Form;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Generic extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        parent::__construct($context, $data);
    }
}
