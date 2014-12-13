<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

class CouponsMassDelete extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Coupons mass delete action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $this->_forward('noroute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = $this->_objectManager->create(
                'Magento\SalesRule\Model\Resource\Coupon\Collection'
            )->addFieldToFilter(
                'coupon_id',
                ['in' => $codesIds]
            );

            foreach ($couponsCollection as $coupon) {
                $coupon->delete();
            }
        }
    }
}
