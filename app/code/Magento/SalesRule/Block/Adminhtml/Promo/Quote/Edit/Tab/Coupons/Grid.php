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
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

/**
 * Coupon codes grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory
     */
    protected $_salesRuleCoupon;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory $salesRuleCoupon
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory $salesRuleCoupon,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_salesRuleCoupon = $salesRuleCoupon;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $priceRule = $this->_coreRegistry->registry('current_promo_quote_rule');

        /**
         * @var \Magento\SalesRule\Model\Resource\Coupon\Collection $collection
         */
        $collection = $this->_salesRuleCoupon->create()->addRuleToFilter($priceRule)->addGeneratedCouponsFilter();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', array('header' => __('Coupon Code'), 'index' => 'code'));

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'align' => 'center',
                'width' => '160'
            )
        );

        $this->addColumn(
            'used',
            array(
                'header' => __('Uses'),
                'index' => 'times_used',
                'width' => '100',
                'type' => 'options',
                'options' => array(__('No'), __('Yes')),
                'renderer' => 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
                'filter_condition_callback' => array($this->_salesRuleCoupon->create(), 'addIsUsedFilterCallback')
            )
        );

        $this->addColumn(
            'times_used',
            array('header' => __('Times Used'), 'index' => 'times_used', 'width' => '50', 'type' => 'number')
        );

        $this->addExportType('*/*/exportCouponsCsv', __('CSV'));
        $this->addExportType('*/*/exportCouponsXml', __('Excel XML'));
        return parent::_prepareColumns();
    }

    /**
     * Configure grid mass actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('sales_rule/*/couponsMassDelete', array('_current' => true)),
                'confirm' => __('Are you sure you want to delete the selected coupon(s)?'),
                'complete' => 'refreshCouponCodesGrid'
            )
        );

        return $this;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales_rule/*/couponsGrid', array('_current' => true));
    }
}
