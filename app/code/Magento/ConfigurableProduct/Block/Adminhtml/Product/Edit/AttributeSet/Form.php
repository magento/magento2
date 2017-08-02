<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute set selector form
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\AttributeSet;

/**
 * @api
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     * @since 2.0.0
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     * @since 2.0.0
     */
    protected $attributeSetOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions,
        array $data = []
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetOptions = $attributeSetOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('product_template_selecte', []);
        $fieldset->addField(
            'current-affected-attribute-set',
            'radio',
            [
                'after_element_html' => __(
                    'Add configurable attributes to the current Attribute Set ("%1")',
                    sprintf('<span data-role="name-container">%s</span>', $this->getCurrentAttributeSetName())
                ),
                'name' => 'affected-attribute-set',
                'class' => 'admin__control-radio',
                'css_class' => 'admin__field-option',
                'checked' => true,
                'value' => 'current'
            ]
        );
        $fieldset->addField(
            'new-affected-attribute-set',
            'radio',
            [
                'after_element_html' => __('Add configurable attributes to the new Attribute Set based on current'),
                'name' => 'affected-attribute-set',
                'class' => 'admin__control-radio',
                'css_class' => 'admin__field-option',
                'value' => 'new'
            ]
        );
        $fieldset->addField(
            'new-attribute-set-name',
            'text',
            [
                'label' => __('New attribute set name'),
                'name' => 'new-attribute-set-name',
                'required' => true,
                'css_class' => 'no-display',
                'field_extra_attributes' => 'data-role="affected-attribute-set-new-name-container"',
                'value' => ''
            ]
        );
        $fieldset->addField(
            'existing-affected-attribute-set',
            'radio',
            [
                'after_element_html' => __('Add configurable attributes to the existing Attribute Set'),
                'name' => 'affected-attribute-set',
                'required' => true,
                'class' => 'admin__control-radio no-display',
                'css_class' => 'admin__field-option',
                'value' => 'existing'
            ]
        );
        $fieldset->addField(
            'choose-affected-attribute-set',
            'select',
            [
                'label' => __('Choose existing Attribute Set'),
                'name' => 'attribute-set-name',
                'required' => true,
                'css_class' => 'no-display',
                'field_extra_attributes' => 'data-role="affected-attribute-set-existing-name-container"',
                'values' => $this->attributeSetOptions->toOptionArray()
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getCurrentAttributeSetName()
    {
        return $this->attributeSetRepository->get(
            $this->_coreRegistry->registry('current_product')->getAttributeSetId()
        )->getAttributeSetName();
    }
}
