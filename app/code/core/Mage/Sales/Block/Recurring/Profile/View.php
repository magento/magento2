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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profile view
 */
class Mage_Sales_Block_Recurring_Profile_View extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Recurring_Profile
     */
    protected $_profile = null;

    /**
     * Whether the block should be used to render $_info
     *
     * @var bool
     */
    protected $_shouldRenderInfo = false;

    /**
     * Information to be rendered
     *
     * @var array
     */
    protected $_info = array();

    /**
     * Related orders collection
     *
     * @var Mage_Sales_Model_Resource_Order_Collection
     */
    protected $_relatedOrders = null;

    /**
     * Prepare main view data
     */
    public function prepareViewData()
    {
        $this->addData(array(
            'reference_id' => $this->_profile->getReferenceId(),
            'can_cancel'   => $this->_profile->canCancel(),
            'cancel_url'   => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'cancel'
                )
            ),
            'can_suspend'  => $this->_profile->canSuspend(),
            'suspend_url'  => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'suspend'
                )
            ),
            'can_activate' => $this->_profile->canActivate(),
            'activate_url' => $this->getUrl(
                '*/*/updateState',
                array(
                    'profile' => $this->_profile->getId(),
                    'action' => 'activate'
                )
            ),
            'can_update'   => $this->_profile->canFetchUpdate(),
            'update_url'   => $this->getUrl(
                '*/*/updateProfile',
                array(
                    'profile' => $this->_profile->getId()
                )
            ),
            'back_url'     => $this->getUrl('*/*/'),
            'confirmation_message' => Mage::helper('Mage_Sales_Helper_Data')->__('Are you sure you want to do this?'),
        ));
    }

    /**
     * Getter for rendered info, if any
     *
     * @return array
     */
    public function getRenderedInfo()
    {
        return $this->_info;
    }

    /**
     * Prepare profile main reference info
     */
    public function prepareReferenceInfo()
    {
        $this->_shouldRenderInfo = true;

        foreach (array('method_code', 'reference_id', 'schedule_description', 'state') as $key) {
            $this->_addInfo(array(
                'label' => $this->_profile->getFieldLabel($key),
                'value' => $this->_profile->renderData($key),
            ));
        }
//        $shippingDesctiption = $this->_profile->getInfoValue('order_info', 'shipping_description');
//        if ($shippingDesctiption) {
//            $this->_addInfo(array(
//                'label' => $this->__('Shipping Method'),
//                'value' => $shippingDesctiption,
//            ));
//        }
    }

    /**
     * Prepare profile order item info
     */
    public function prepareItemInfo()
    {
        $this->_shouldRenderInfo = true;
        $key = 'order_item_info';

        foreach (array('name' => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Name'),
            'sku'  => Mage::helper('Mage_Catalog_Helper_Data')->__('SKU'),
            'qty'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Quantity'),
            ) as $itemKey => $label
        ) {
            $value = $this->_profile->getInfoValue($key, $itemKey);
            if ($value) {
                $this->_addInfo(array('label' => $label, 'value' => $value,));
            }
        }

        $request = $this->_profile->getInfoValue($key, 'info_buyRequest');
        if (empty($request)) {
            return;
        }

        $request = unserialize($request);
        if (empty($request['options'])) {
            return;
        }

        $options = Mage::getModel('Mage_Catalog_Model_Product_Option')->getCollection()
            ->addIdsToFilter(array_keys($request['options']))
            ->addTitleToResult($this->_profile->getInfoValue($key, 'store_id'))
            ->addValuesToResult();

        $productMock = Mage::getModel('Mage_Catalog_Model_Product');
        $quoteItemOptionMock = Mage::getModel('Mage_Sales_Model_Quote_Item_Option');
        foreach ($options as $option) {
            $quoteItemOptionMock->setId($option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setRequest(new Varien_Object($request))
                ->setProduct($productMock)
                ->setUseQuotePath(true)
                ->setQuoteItemOption($quoteItemOptionMock)
                ->validateUserValue($request['options']);

            $skipHtmlEscaping = false;
            if ('file' == $option->getType()) {
                $skipHtmlEscaping = true;

                $downloadParams = array(
                    'id'  => $this->_profile->getId(),
                    'option_id' => $option->getId(),
                    'key' => $request['options'][$option->getId()]['secret_key']
                );
                $group->setCustomOptionDownloadUrl('sales/download/downloadProfileCustomOption')
                    ->setCustomOptionUrlParams($downloadParams);
            }

            $optionValue = $group->prepareForCart();

            $this->_addInfo(array(
                'label' => $option->getTitle(),
                'value' => $group->getFormattedOptionValue($optionValue),
                'skip_html_escaping' => $skipHtmlEscaping
            ));
        }
    }

    /**
     * Prepare profile schedule info
     */
    public function prepareScheduleInfo()
    {
        $this->_shouldRenderInfo = true;

        foreach (array('start_datetime', 'suspension_threshold') as $key) {
            $this->_addInfo(array(
                'label' => $this->_profile->getFieldLabel($key),
                'value' => $this->_profile->renderData($key),
            ));
        }

        foreach ($this->_profile->exportScheduleInfo() as $i) {
            $this->_addInfo(array(
                'label' => $i->getTitle(),
                'value' => $i->getSchedule(),
            ));
        }
    }

    /**
     * Prepare profile payments info
     */
    public function prepareFeesInfo()
    {
        $this->_shouldRenderInfo = true;

        $this->_addInfo(array(
            'label' => $this->_profile->getFieldLabel('currency_code'),
            'value' => $this->_profile->getCurrencyCode()
        ));
        $params = array('init_amount', 'trial_billing_amount', 'billing_amount', 'tax_amount', 'shipping_amount');
        foreach ($params as $key) {
            $value = $this->_profile->getData($key);
            if ($value) {
                $this->_addInfo(array(
                    'label' => $this->_profile->getFieldLabel($key),
                    'value' => Mage::helper('Mage_Core_Helper_Data')->formatCurrency($value, false),
                    'is_amount' => true,
                ));
            }
        }
    }

    /**
     * Prepare profile address (billing or shipping) info
     */
    public function prepareAddressInfo()
    {
        $this->_shouldRenderInfo = true;

        if ('shipping' == $this->getAddressType()) {
            if ('1' == $this->_profile->getInfoValue('order_item_info', 'is_virtual')) {
                $this->getParentBlock()->unsetChild('sales.recurring.profile.view.shipping');
                return;
            }
            $key = 'shipping_address_info';
        } else {
            $key = 'billing_address_info';
        }
        $this->setIsAddress(true);
        $address = Mage::getModel('Mage_Sales_Model_Order_Address', $this->_profile->getData($key));
        $this->_addInfo(array(
            'value' => preg_replace('/\\n{2,}/', "\n", $address->getFormated()),
        ));
    }

    /**
     * Render related orders grid information
     */
    public function prepareRelatedOrdersFrontendGrid()
    {
        $this->_prepareRelatedOrders(array(
            'increment_id', 'created_at', 'customer_firstname', 'customer_lastname', 'base_grand_total', 'status'
        ));
        $this->_relatedOrders->addFieldToFilter('state', array(
            'in' => Mage::getSingleton('Mage_Sales_Model_Order_Config')->getVisibleOnFrontStates()
        ));

        $pager = $this->getLayout()->createBlock('Mage_Page_Block_Html_Pager')
            ->setCollection($this->_relatedOrders)->setIsOutputRequired(false);
        $this->setChild('pager', $pager);

        $this->setGridColumns(array(
            new Varien_Object(array(
                'index' => 'increment_id',
                'title' => $this->__('Order #'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new Varien_Object(array(
                'index' => 'created_at',
                'title' => $this->__('Date'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new Varien_Object(array(
                'index' => 'customer_name',
                'title' => $this->__('Customer Name'),
            )),
            new Varien_Object(array(
                'index' => 'base_grand_total',
                'title' => $this->__('Order Total'),
                'is_nobr' => true,
                'width' => 1,
                'is_amount' => true,
            )),
            new Varien_Object(array(
                'index' => 'status',
                'title' => $this->__('Order Status'),
                'is_nobr' => true,
                'width' => 1,
            )),
        ));

        $orders = array();
        foreach ($this->_relatedOrders as $order) {
            $orders[] = new Varien_Object(array(
                'increment_id' => $order->getIncrementId(),
                'created_at' => $this->formatDate($order->getCreatedAt()),
                'customer_name' => $order->getCustomerName(),
                'base_grand_total' => Mage::helper('Mage_Core_Helper_Data')->formatCurrency(
                    $order->getBaseGrandTotal(), false
                ),
                'status' => $order->getStatusLabel(),
                'increment_id_link_url' => $this->getUrl('sales/order/view/', array('order_id' => $order->getId())),
            ));
        }
        if ($orders) {
            $this->setGridElements($orders);
        }
    }

    /**
     * Get rendered row value
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderRowValue(Varien_Object $row)
    {
        $value = $row->getValue();
        if (is_array($value)) {
            $value = implode("\n", $value);
        }
        if (!$row->getSkipHtmlEscaping()) {
            $value = $this->escapeHtml($value);
        }
        return nl2br($value);
    }

    /**
     * Prepare related orders collection
     *
     * @param array|string $fieldsToSelect
     */
    protected function _prepareRelatedOrders($fieldsToSelect = '*')
    {
        if (null === $this->_relatedOrders) {
            $this->_relatedOrders = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Collection')
                ->addFieldToSelect($fieldsToSelect)
                ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
                ->addRecurringProfilesFilter($this->_profile->getId())
                ->setOrder('entity_id', 'desc');
        }
    }

    /**
     * Add specified data to the $_info
     *
     * @param array $data
     * @param string $key = null
     */
    protected function _addInfo(array $data, $key = null)
    {
        $object = new Varien_Object($data);
        if ($key) {
            $this->_info[$key] = $object;
        } else {
            $this->_info[] = $object;
        }
    }

    /**
     * Get current profile from registry and assign store/locale information to it
     */
    protected function _prepareLayout()
    {
        $this->_profile = Mage::registry('current_recurring_profile')
            ->setStore(Mage::app()->getStore())
            ->setLocale(Mage::app()->getLocale())
        ;
        return parent::_prepareLayout();
    }

    /**
     * Render self only if needed, also render info tabs group if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_profile || $this->_shouldRenderInfo && !$this->_info) {
            return '';
        }

        if ($this->hasShouldPrepareInfoTabs()) {
            foreach ($this->getChildGroup('info_tabs') as $block) {
                $block->setViewUrl(
                    $this->getUrl("*/*/{$block->getViewAction()}", array('profile' => $this->_profile->getId()))
                );
            }
        }

        return parent::_toHtml();
    }
}
