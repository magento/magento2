<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

class CouponsMassDelete extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Coupons mass delete action
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $this->_initRule();
        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule->getId()) {
            $this->_forward('noroute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = $this->_objectManager->create(
                \Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class
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
