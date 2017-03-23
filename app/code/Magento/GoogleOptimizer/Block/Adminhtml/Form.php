<?php
/**
 * Google Optimizer Category Tab
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends Generic
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Form
     */
    protected $formHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\GoogleOptimizer\Helper\Form $formHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\GoogleOptimizer\Helper\Form $formHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->formHelper = $formHelper;
        $this->setForm($formFactory->create());
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $entityClass = $this->getData('code-entity');
        $formName = $this->getData('form-name');
        if (!$entityClass) {
            throw new ConfigurationMismatchException(__('Data key is missing: %1', ['code-entity']));
        }
        if (!$formName) {
            throw new ConfigurationMismatchException(__('Data key is missing: %1', ['form-name']));
        }

        $entity = ObjectManager::getInstance()->create($entityClass);

        $this->formHelper->addGoogleoptimizerFields($this->getForm(), $entity->getCode(), $formName);
        $this->getForm()->getElement('googleoptimizer_fields')->setData(['legend' => null]);
        return parent::_prepareForm();
    }
}
