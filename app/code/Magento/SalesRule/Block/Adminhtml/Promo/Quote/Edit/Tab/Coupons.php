<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

/**
 * "Manage Coupons Codes" Tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coupons extends \Magento\Backend\Block\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Manage Coupon Codes');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Manage Coupon Codes');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setCanSHow($canShow)
    {
        $this->_data['config']['canShow'] = $canShow;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $model = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        $disableInputFields = ! $model->getUseAutoGeneration();
        // @todo: remove this workaround after resolving MAGETWO-48846
        // @codingStandardsIgnoreStart
        $html = <<<HTML_ENTITIES
<script>
function disableEnableCouponTabInputFields(isDisabled) {
    var selector = '[id=coupons_information_fieldset] input, [id=coupons_information_fieldset] select, [id=coupons_information_fieldset] button, [id=couponCodesGrid] input, [id=couponCodesGrid] select, [id=couponCodesGrid] button';

    _.each(
        document.querySelectorAll(selector),
        function (element) {
            element.disabled = isDisabled;
        }
    );
}
disableEnableCouponTabInputFields({$disableInputFields});
</script>
HTML_ENTITIES;
        // @codingStandardsIgnoreEnd

        return parent::toHtml() . $html;
    }
}
