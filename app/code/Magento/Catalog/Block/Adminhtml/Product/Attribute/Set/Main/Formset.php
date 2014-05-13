<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main;

use Magento\Backend\Block\Widget\Form;

class Formset extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_setFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        array $data = array()
    ) {
        $this->_setFactory = $setFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        $data = $this->_setFactory->create()->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('set_name', array('legend' => __('Edit Set Name')));
        $fieldset->addField(
            'attribute_set_name',
            'text',
            array(
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry validate-no-html-tags',
                'value' => $data->getAttributeSetName()
            )
        );

        if (!$this->getRequest()->getParam('id', false)) {
            $fieldset->addField('gotoEdit', 'hidden', array('name' => 'gotoEdit', 'value' => '1'));

            $sets = $this->_setFactory->create()->getResourceCollection()->setEntityTypeFilter(
                $this->_coreRegistry->registry('entityType')
            )->load()->toOptionArray();

            $fieldset->addField(
                'skeleton_set',
                'select',
                array(
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $sets
                )
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
