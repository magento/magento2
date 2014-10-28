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
 * Adminhtml Review Edit Form
 */
namespace Magento\Review\Block\Adminhtml\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $customerAccount;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

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
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccount
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Helper\Data $reviewData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccount,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Helper\Data $reviewData,
        array $data = array()
    ) {
        $this->_reviewData = $reviewData;
        $this->customerAccount = $customerAccount;
        $this->_productFactory = $productFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare edit review form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $review = $this->_coreRegistry->registry('review_data');
        $product = $this->_productFactory->create()->load($review->getEntityPkValue());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'review/*/save',
                        array(
                            'id' => $this->getRequest()->getParam('id'),
                            'ret' => $this->_coreRegistry->registry('ret')
                        )
                    ),
                    'method' => 'post'
                )
            )
        );

        $fieldset = $form->addFieldset(
            'review_details',
            array('legend' => __('Review Details'), 'class' => 'fieldset-wide')
        );

        $fieldset->addField(
            'product_name',
            'note',
            array(
                'label' => __('Product'),
                'text' => '<a href="' . $this->getUrl(
                    'catalog/product/edit',
                    array('id' => $product->getId())
                ) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
                    $product->getName()
                ) . '</a>'
            )
        );

        try {
            $customer = $this->customerAccount->getCustomer($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', array('id' => $customer->getId(), 'active_tab'=>'review')),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }

        $fieldset->addField('customer', 'note', array('label' => __('Posted By'), 'text' => $customerText));

        $fieldset->addField(
            'summary_rating',
            'note',
            array(
                'label' => __('Summary Rating'),
                'text' => $this->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Rating\Summary')->toHtml()
            )
        );

        $fieldset->addField(
            'detailed_rating',
            'note',
            array(
                'label' => __('Detailed Rating'),
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
        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                array(
                    'label' => __('Visible In'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $review->setSelectStores($review->getStores());
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                array('name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId())
            );
            $review->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'nickname',
            'text',
            array('label' => __('Nickname'), 'required' => true, 'name' => 'nickname')
        );

        $fieldset->addField(
            'title',
            'text',
            array('label' => __('Summary of Review'), 'required' => true, 'name' => 'title')
        );

        $fieldset->addField(
            'detail',
            'textarea',
            array('label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:24em;')
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
