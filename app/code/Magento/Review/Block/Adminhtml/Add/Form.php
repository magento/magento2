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
namespace Magento\Review\Block\Adminhtml\Add;

/**
 * Adminhtml add product review form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * Core system store model
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Review\Helper\Data $reviewData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Review\Helper\Data $reviewData,
        array $data = array()
    ) {
        $this->_reviewData = $reviewData;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare add review form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('add_review_form', array('legend' => __('Review Details')));

        $fieldset->addField('product_name', 'note', array('label' => __('Product'), 'text' => 'product_name'));

        $fieldset->addField(
            'detailed_rating',
            'note',
            array(
                'label' => __('Product Rating'),
                'required' => true,
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    'Magento\Review\Block\Adminhtml\Rating\Detailed'
                )->toHtml() . '</div>'
            )
        );

        $fieldset->addField(
            'status_id',
            'select',
            array(
                'label' => __('Status'),
                'required' => true,
                'name' => 'status_id',
                'values' => $this->_reviewData->getReviewStatusesOptionArray()
            )
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                array(
                    'label' => __('Visible In'),
                    'required' => true,
                    'name' => 'select_stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'nickname',
            'text',
            array(
                'name' => 'nickname',
                'title' => __('Nickname'),
                'label' => __('Nickname'),
                'maxlength' => '50',
                'required' => true
            )
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'name' => 'title',
                'title' => __('Summary of Review'),
                'label' => __('Summary of Review'),
                'maxlength' => '255',
                'required' => true
            )
        );

        $fieldset->addField(
            'detail',
            'textarea',
            array(
                'name' => 'detail',
                'title' => __('Review'),
                'label' => __('Review'),
                'style' => 'height: 600px;',
                'required' => true
            )
        );

        $fieldset->addField('product_id', 'hidden', array('name' => 'product_id'));

        /*$gridFieldset = $form->addFieldset('add_review_grid', array('legend' => __('Please select a product')));
          $gridFieldset->addField('products_grid', 'note', array(
          'text' => $this->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Product\Grid')->toHtml(),
          ));*/

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('review/product/post'));

        $this->setForm($form);
    }
}
