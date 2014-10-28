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
        array $data = array()
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

        $fieldset = $form->addFieldset('set_fieldset', array('legend' => __('Add New Group')));

        $fieldset->addField(
            'attribute_group_name',
            'text',
            array('label' => __('Name'), 'name' => 'attribute_group_name', 'required' => true)
        );

        $fieldset->addField(
            'submit',
            'note',
            array(
                'text' => $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Button'
                )->setData(
                    array('label' => __('Add Group'), 'onclick' => 'this.form.submit();', 'class' => 'add')
                )->toHtml()
            )
        );

        $fieldset->addField(
            'attribute_set_id',
            'hidden',
            array('name' => 'attribute_set_id', 'value' => $this->_getSetId())
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
