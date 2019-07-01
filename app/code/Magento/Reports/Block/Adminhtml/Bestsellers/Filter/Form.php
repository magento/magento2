<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Bestsellers\Filter;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Reports\Block\Adminhtml\Filter\Form as ReportsBlockFilterForm;

/**
 * Sales Adminhtml report bestseller filter form
 *
 * @package Magento\Reports\Block\Adminhtml\Bestsellers\Filter
 * @api
 */
class Form extends ReportsBlockFilterForm
{
    /**
     * Sorting limits for items displayed
     *
     * @var []
     */
    private $itemSortLimit;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $itemSortLimit
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $itemSortLimit,
        array $data = []
    ) {
        $this->itemSortLimit = $itemSortLimit;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function _prepareForm()
    {
        parent::_prepareForm();
        /** @var Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');
        if ($fieldset instanceof Fieldset) {
            $fieldset->addField(
                'rating_limit',
                'select',
                [
                    'name' => 'rating_limit',
                    'label' => __('Display items'),
                    'options' => $this->itemSortLimit
                ],
                'to'
            );
        }

        return $this;
    }
}
