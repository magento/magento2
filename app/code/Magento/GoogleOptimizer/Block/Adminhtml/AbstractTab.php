<?php
/**
 * Abstract Google Experiment Tab
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml;

/**
 * Class \Magento\GoogleOptimizer\Block\Adminhtml\AbstractTab
 *
 * @since 2.0.0
 */
abstract class AbstractTab extends \Magento\Backend\Block\Widget\Form implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     * @since 2.0.0
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code
     * @since 2.0.0
     */
    protected $_codeHelper;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Form
     * @since 2.0.0
     */
    protected $_formHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\GoogleOptimizer\Helper\Data $helperData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GoogleOptimizer\Helper\Code $codeHelper
     * @param \Magento\GoogleOptimizer\Helper\Form $formHelper
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\GoogleOptimizer\Helper\Data $helperData,
        \Magento\Framework\Registry $registry,
        \Magento\GoogleOptimizer\Helper\Code $codeHelper,
        \Magento\GoogleOptimizer\Helper\Form $formHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_helperData = $helperData;
        $this->_registry = $registry;
        $this->_codeHelper = $codeHelper;
        $this->_formHelper = $formHelper;
        $this->setForm($formFactory->create());
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        $this->_formHelper->addGoogleoptimizerFields($this->getForm(), $this->_getGoogleExperiment());
        return parent::_prepareForm();
    }

    /**
     * Get google experiment code model
     *
     * @return \Magento\GoogleOptimizer\Model\Code|null
     * @since 2.0.0
     */
    protected function _getGoogleExperiment()
    {
        $entity = $this->_getEntity();
        if ($entity->getId()) {
            return $this->_codeHelper->getCodeObjectByEntity($entity);
        }
        return null;
    }

    /**
     * Get Entity model
     *
     * @return \Magento\Catalog\Model\AbstractModel
     * @since 2.0.0
     */
    abstract protected function _getEntity();

    /**
     * Can show tab in tabs
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->_helperData->isGoogleExperimentActive();
    }

    /**
     * Tab is hidden
     *
     * @return bool
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }
}
