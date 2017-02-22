<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
                'Magento\SalesRule\Model\ResourceModel\Coupon\Collection'
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
