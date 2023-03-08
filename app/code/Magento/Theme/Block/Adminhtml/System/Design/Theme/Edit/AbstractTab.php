<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Theme\Model\Theme;

/**
 * Theme form tab abstract block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
abstract class AbstractTab extends Generic implements TabInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * Get theme that is edited currently
     *
     * @return Theme
     */
    protected function _getCurrentTheme()
    {
        return $this->_coreRegistry->registry('current_theme');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->_getCurrentTheme()->isVirtual() && $this->_getCurrentTheme()->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
