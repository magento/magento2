<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main;

use Magento\Backend\Block\Widget\Form;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset
 *
 * @since 2.0.0
 */
class Formset extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     * @since 2.0.0
     */
    protected $_setFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        array $data = []
    ) {
        $this->_setFactory = $setFactory;
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
        $data = $this->_setFactory->create()->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('set_name', ['legend' => __('Edit Attribute Set Name')]);
        $fieldset->addField(
            'attribute_set_name',
            'text',
            [
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry validate-no-html-tags',
                'value' => $data->getAttributeSetName()
            ]
        );

        if (!$this->getRequest()->getParam('id', false)) {
            $fieldset->addField('gotoEdit', 'hidden', ['name' => 'gotoEdit', 'value' => '1']);

            $sets = $this->_setFactory->create()->getResourceCollection()->setEntityTypeFilter(
                $this->_coreRegistry->registry('entityType')
            )->load()->toOptionArray();

            $fieldset->addField(
                'skeleton_set',
                'select',
                [
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $sets
                ]
            );
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set-prop-form');
        $form->setAction($this->getUrl('catalog/*/save'));
        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }
}
