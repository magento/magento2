<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\SalesRule\Controller\Adminhtml\Promo\Quote as AdminhtmlPromoQuote;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection as CouponCollection;

class CouponsMassDelete extends AdminhtmlPromoQuote
{
    /**
     * Coupons mass delete action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule->getId()) {
            $this->_forward('noroute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = $this->_objectManager->create(
                CouponCollection::class
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
