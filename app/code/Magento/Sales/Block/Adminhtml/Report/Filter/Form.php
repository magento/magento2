<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Report\Filter;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\ConfigFactory;
use Magento\Reports\Block\Adminhtml\Filter\Form as ReportsBlockFilterForm;

/**
 * Sales Adminhtml report filter form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since      100.0.2
 * @deprecated
 * @see        \Magento\Reports\Block\Adminhtml\Bestsellers\Filter\Form
 */
class Form extends ReportsBlockFilterForm
{
    /**
     * Order config
     *
     * @var ConfigFactory
     */
    protected $_orderConfig;

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
     * @param ConfigFactory $orderConfig
     * @param array $data
     * @param array $itemSortLimit
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ConfigFactory $orderConfig,
        array $data = [],
        array $itemSortLimit = []
    ) {
        $this->itemSortLimit = $itemSortLimit;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return $this|ReportsBlockFilterForm
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Fieldset) {
            $statuses = $this->_orderConfig->create()->getStatuses();
            $values = [];
            foreach ($statuses as $code => $label) {
                if (false === strpos($code, 'pending')) {
                    $values[] = ['label' => __($label), 'value' => $code];
                }
            }

            $fieldset->addField(
                'show_order_statuses',
                'select',
                [
                    'name' => 'show_order_statuses',
                    'label' => __('Order Status'),
                    'options' => ['0' => __('Any'), '1' => __('Specified')],
                    'note' => __('Applies to Any of the Specified Order Statuses except canceled orders')
                ],
                'to'
            );

            $fieldset->addField(
                'order_statuses',
                'multiselect',
                [
                    'name' => 'order_statuses',
                    'label' => '',
                    'values' => $values,
                    'display' => 'none'
                ],
                'show_order_statuses'
            );
            $this->setBestsellersItemLimit($fieldset);

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                $this->setChild(
                    'form_after',
                    $this->getLayout()->createBlock(
                        \Magento\Backend\Block\Widget\Form\Element\Dependence::class
                    )->addFieldMap(
                        "{$htmlIdPrefix}show_order_statuses",
                        'show_order_statuses'
                    )->addFieldMap(
                        "{$htmlIdPrefix}order_statuses",
                        'order_statuses'
                    )->addFieldDependence(
                        'order_statuses',
                        'show_order_statuses',
                        '1'
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Element for setting bestsellers limit
     *
     * @param Fieldset $fieldset
     */
    private function setBestsellersItemLimit(Fieldset $fieldset)
    {
        if ($this->getData('report_type') == 'bestseller') {
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
    }
}
