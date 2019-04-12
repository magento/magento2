<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

/**
 * Bundle Extended Attribures Block.
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Extend extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * Initialize block template
     */
    private $template = 'Magento_Bundle::catalog/product/edit/tab/attributes/extend.phtml';

    const DYNAMIC = 0;

    const FIXED = 1;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

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
        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
    }

    /**
     * Class constructor
     *
     * @return void
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
     */
    public function getElementHtml()
    {
        $templateFile = $this->getTemplateFile($this->template);
        return $this->fetchView($templateFile);
    }

    /**
     * Execute method getElementHtml from parent class
     *
     * @return string
     */
    public function getParentElementHtml()
    {
        return parent::getElementHtml();
    }

    /**
     * Get options.
     *
     * @return array
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
     * Is disabled field.
     *
     * @return bool
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
     * Get product.
     *
     * @return mixed
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Get extended element.
     *
     * @param string $switchAttributeCode
     * @return \Magento\Framework\Data\Form\Element\Select
     * @throws \Magento\Framework\Exception\LocalizedException
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
                'class' => 'required-entry next-toinput',
                'no_span' => true,
                'disabled' => $this->isDisabledField(),
                'value' => $this->getProduct()->getData($switchAttributeCode),
            ]
        );
    }
}
