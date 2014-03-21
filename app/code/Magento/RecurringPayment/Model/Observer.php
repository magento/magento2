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
namespace Magento\RecurringPayment\Model;

/**
 * Recurring payment observer
 */
class Observer
{
    /**
     * Locale model
     *
     * @var \Magento\LocaleInterface
     */
    protected $_locale;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Recurring payment factory
     *
     * @var \Magento\RecurringPayment\Model\RecurringPaymentFactory
     */
    protected $_recurringPaymentFactory;

    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\LocaleInterface $locale
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\RecurringPayment\Model\RecurringPaymentFactory  $recurringPaymentFactory
     * @param \Magento\RecurringPayment\Block\Fields $fields
     */
    public function __construct(
        \Magento\LocaleInterface $locale,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\RecurringPayment\Model\RecurringPaymentFactory $recurringPaymentFactory,
        \Magento\RecurringPayment\Block\Fields $fields
    ) {
        $this->_locale = $locale;
        $this->_storeManager = $storeManager;
        $this->_recurringPaymentFactory = $recurringPaymentFactory;
        $this->_fields = $fields;
    }

    /**
     * Collect buy request and set it as custom option
     *
     * Also sets the collected information and schedule as informational static options
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function prepareProductRecurringPaymentOptions($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        if (!$product->getIsRecurring()) {
            return;
        }

        /** @var \Magento\RecurringPayment\Model\RecurringPayment $payment */
        $payment = $this->_recurringPaymentFactory->create(array('locale' => $this->_locale));
        $payment->setStore($this->_storeManager->getStore())->importBuyRequest($buyRequest)->importProduct($product);
        if (!$payment) {
            return;
        }

        // add the start datetime as product custom option
        $product->addCustomOption(
            \Magento\RecurringPayment\Model\RecurringPayment::PRODUCT_OPTIONS_KEY,
            serialize(array('start_datetime' => $payment->getStartDatetime()))
        );

        // duplicate as 'additional_options' to render with the product statically
        $infoOptions = array(
            array(
                'label' => $this->_fields->getFieldLabel('start_datetime'),
                'value' => $payment->exportStartDatetime()
            )
        );

        foreach ($payment->exportScheduleInfo() as $info) {
            $infoOptions[] = array('label' => $info->getTitle(), 'value' => $info->getSchedule());
        }
        $product->addCustomOption('additional_options', serialize($infoOptions));
    }

    /**
     * Unserialize product recurring payment
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function unserializeProductRecurringPayment($observer)
    {
        $collection = $observer->getEvent()->getCollection();

        foreach ($collection as $product) {
            if ($product->getIsRecurring() && ($payment = $product->getRecurringPayment())) {
                $product->setRecurringPayment(unserialize($payment));
            }
        }
    }

    /**
     * Set recurring data to quote
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function setIsRecurringToQuote($observer)
    {
        $quote = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();

        $quote->setIsRecurring($product->getIsRecurring());
    }

    /**
     * Add recurring payment field to excluded list
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function addFormExcludedAttribute($observer)
    {
        $block = $observer->getEvent()->getObject();

        $block->setFormExcludedFieldList(array_merge($block->getFormExcludedFieldList(), array('recurring_payment')));
    }

    /**
     * Set recurring payment renderer
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function setFormRecurringElementRenderer($observer)
    {
        $form = $observer->getEvent()->getForm();

        $recurringPaymentElement = $form->getElement('recurring_payment');
        $recurringPaymentBlock = $observer->getEvent()->getLayout()->createBlock(
            'Magento\RecurringPayment\Block\Adminhtml\Product\Edit\Tab\Price\Recurring'
        );

        if ($recurringPaymentElement) {
            $recurringPaymentElement->setRenderer($recurringPaymentBlock);
        }
    }
}
