<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main;

use Magento\Backend\Block\Widget\Form;

class Formgroup extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory $typeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('set_fieldset', ['legend' => __('Add New Group')]);

        $fieldset->addField(
            'attribute_group_name',
            'text',
            ['label' => __('Name'), 'name' => 'attribute_group_name', 'required' => true]
        );

        $fieldset->addField(
            'submit',
            'note',
            [
                'text' => $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Button'
                )->setData(
                    ['label' => __('Add Group'), 'onclick' => 'this.form.submit();', 'class' => 'add']
                )->toHtml()
            ]
        );

        $fieldset->addField(
            'attribute_set_id',
            'hidden',
            ['name' => 'attribute_set_id', 'value' => $this->_getSetId()]
        );

        $form->setUseContainer(true);
        $form->setMethod('post');
        $form->setAction($this->getUrl('catalog/product_group/save'));
        $this->setForm($form);
    }

    /**
     * @return int
     */
    protected function _getSetId()
    {
        return intval(
            $this->getRequest()->getParam('id')
        ) > 0 ? intval(
            $this->getRequest()->getParam('id')
        ) : $this->_typeFactory->create()->load(
            $this->_coreRegistry->registry('entityType')
        )->getDefaultAttributeSetId();
    }
}
