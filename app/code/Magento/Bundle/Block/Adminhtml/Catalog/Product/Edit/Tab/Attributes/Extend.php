<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bundle Extended Attribures Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

/**
 * Class \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend
 *
 * @since 2.0.0
 */
class Extend extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * Initialize block template
     * @since 2.0.0
     */
    private $template = 'Magento_Bundle::catalog/product/edit/tab/attributes/extend.phtml';

    const DYNAMIC = 0;

    const FIXED = 1;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\FormFactory
     * @since 2.0.0
     */
    private $formFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Get Element Html
     *
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $templateFile = $this->getTemplateFile($this->template);
        return $this->fetchView($templateFile);
    }

    /**
     * Execute method getElementHtml from parrent class
     *
     * @return string
     * @since 2.0.0
     */
    public function getParentElementHtml()
    {
        return parent::getElementHtml();
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getOptions()
    {
        return [
            [
                'value' => '',
                'label' => __('-- Select --')
            ],
            [
                'value' => self::DYNAMIC,
                'label' => __('Dynamic')
            ],
            [
                'value' => self::FIXED,
                'label' => __('Fixed')
            ]
        ];
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isDisabledField()
    {
        return $this->_getData('is_disabled_field')
            || ($this->getProduct()->getId()
                && $this->getAttribute()->getAttributeCode() === 'price'
            )
            || $this->getElement()->getReadonly();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * @param string $switchAttributeCode
     * @return \Magento\Framework\Data\Form\Element\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getExtendedElement($switchAttributeCode)
    {
        $form = $this->formFactory->create();
        return $form->addField(
            $switchAttributeCode,
            'select',
            [
                'name' => "product[{$switchAttributeCode}]",
                'values' => $this->getOptions(),
                'value' => $switchAttributeCode,
                'class' => 'required-entry next-toinput',
                'no_span' => true,
                'disabled' => $this->isDisabledField(),
                'value' => $this->getProduct()->getData($switchAttributeCode),
            ]
        );
    }
}
