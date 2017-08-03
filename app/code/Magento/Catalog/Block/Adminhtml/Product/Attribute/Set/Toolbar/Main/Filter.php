<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main;

use Magento\Backend\Block\Widget\Form;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main\Filter
 *
 * @since 2.0.0
 */
class Filter extends \Magento\Backend\Block\Widget\Form\Generic
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
     * @return void
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $collection = $this->_setFactory->create()->getResourceCollection()->load()->toOptionArray();

        $form->addField(
            'set_switcher',
            'select',
            [
                'name' => 'set_switcher',
                'required' => true,
                'class' => 'left-col-block',
                'no_span' => true,
                'values' => $collection,
                'onchange' => 'this.form.submit()'
            ]
        );

        $form->setUseContainer(true);
        $form->setMethod('post');
        $this->setForm($form);
    }
}
