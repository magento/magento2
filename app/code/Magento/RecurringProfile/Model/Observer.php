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

namespace Magento\RecurringProfile\Model;

/**
 * Recurring profile observer
 */
class Observer
{
    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Recurring profile factory
     *
     * @var \Magento\RecurringProfile\Model\RecurringProfileFactory
     */
    protected $_recurringProfileFactory;

    /**
     * @var \Magento\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\RecurringProfile\Block\Fields
     */
    protected $_fields;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Quote
     */
    protected $_quoteImporter;

    /**
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\RecurringProfile\Model\RecurringProfileFactory $recurringProfileFactory
     * @param \Magento\View\Element\BlockFactory $blockFactory
     * @param \Magento\RecurringProfile\Block\Fields $fields
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteImporter $quoteImporter
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\RecurringProfile\Model\RecurringProfileFactory $recurringProfileFactory,
        \Magento\View\Element\BlockFactory $blockFactory,
        \Magento\RecurringProfile\Block\Fields $fields,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\RecurringProfile\Model\QuoteImporter $quoteImporter
    ) {
        $this->_locale = $locale;
        $this->_storeManager = $storeManager;
        $this->_recurringProfileFactory = $recurringProfileFactory;
        $this->_blockFactory = $blockFactory;
        $this->_fields = $fields;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteImporter = $quoteImporter;
    }

    /**
     * Collect buy request and set it as custom option
     *
     * Also sets the collected information and schedule as informational static options
     *
     * @param \Magento\Event\Observer $observer
     */
    public function prepareProductRecurringProfileOptions($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        if (!$product->isRecurring()) {
            return;
        }

        /** @var \Magento\RecurringProfile\Model\RecurringProfile $profile */
        $profile = $this->_recurringProfileFactory->create(['locale' => $this->_locale]);
        $profile->setStore($this->_storeManager->getStore())
            ->importBuyRequest($buyRequest)
            ->importProduct($product);
        if (!$profile) {
            return;
        }

        // add the start datetime as product custom option
        $product->addCustomOption(\Magento\RecurringProfile\Model\RecurringProfile::PRODUCT_OPTIONS_KEY,
            serialize(array('start_datetime' => $profile->getStartDatetime()))
        );

        // duplicate as 'additional_options' to render with the product statically
        $infoOptions = array(array(
            'label' => $this->_fields->getFieldLabel('start_datetime'),
            'value' => $profile->exportStartDatetime(),
        ));

        foreach ($profile->exportScheduleInfo() as $info) {
            $infoOptions[] = array(
                'label' => $info->getTitle(),
                'value' => $info->getSchedule(),
            );
        }
        $product->addCustomOption('additional_options', serialize($infoOptions));
    }

    /**
     * Add the recurring profile form when editing a product
     *
     * @param \Magento\Event\Observer $observer
     */
    public function addFieldsToProductEditForm($observer)
    {
        // replace the element of recurring payment profile field with a form
        $profileElement = $observer->getEvent()->getProductElement();
        $product = $observer->getEvent()->getProduct();

        /** @var $formBlock \Magento\RecurringProfile\Block\Adminhtml\Profile\Edit\Form */
        $formBlock = $this->_blockFactory->createBlock('Magento\RecurringProfile\Block\Adminhtml\Profile\Edit\Form');
        $formBlock->setNameInLayout('adminhtml_recurring_profile_edit_form');
        $formBlock->setParentElement($profileElement);
        $formBlock->setProductEntity($product);
        $output = $formBlock->toHtml();

        // make the profile element dependent on is_recurring
        /** @var $dependencies \Magento\Backend\Block\Widget\Form\Element\Dependence */
        $dependencies = $this->_blockFactory->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
        $dependencies->setNameInLayout('adminhtml_recurring_profile_edit_form_dependence');
        $dependencies->addFieldMap('is_recurring', 'product[is_recurring]');
        $dependencies->addFieldMap($profileElement->getHtmlId(), $profileElement->getName());
        $dependencies->addFieldDependence($profileElement->getName(), 'product[is_recurring]', '1');
        $dependencies->addConfigOptions(array('levels_up' => 2));

        $output .= $dependencies->toHtml();

        $observer->getEvent()->getResult()->output = $output;
    }

    /**
     * Submit recurring profiles
     *
     * @param \Magento\Event\Observer $observer
     * @throws \Magento\Core\Exception
     */
    public function submitRecurringPaymentProfiles($observer)
    {
        $profiles = $this->_quoteImporter->prepareRecurringPaymentProfiles($observer->getEvent()->getQuote());
        foreach ($profiles as $profile) {
            if (!$profile->isValid()) {
                throw new \Magento\Core\Exception($profile->getValidationErrors());
            }
            $profile->submit();
        }
    }

    public function addRecurringProfileIdsToSession($observer)
    {
        $profiles = $this->_quoteImporter->prepareRecurringPaymentProfiles($observer->getEvent()->getQuote());
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
        }
    }
}
