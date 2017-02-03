<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

class Generate extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Generate Coupons action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        $result = [];
        $this->_initRule();

        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                $data = array_merge($data, $rule->getData(), ['usage_per_customer' => $rule->getUsesPerCustomer()]);

                if (!empty($data['to_date'])) {
                    $inputFilter = new \Zend_Filter_Input(['to_date' => $this->_dateFilter], [], $data);
                    $data = $inputFilter->getUnescaped();
                }

                /** @var $generator \Magento\SalesRule\Model\Coupon\Massgenerator */
                $generator = $this->_objectManager->get(\Magento\SalesRule\Model\Coupon\Massgenerator::class);
                if (!$generator->validateData($data)) {
                    $result['error'] = __('Invalid data provided');
                } else {
                    $generator->setData($data);
                    $generator->generatePool();
                    $generated = $generator->getGeneratedCount();
                    $this->messageManager->addSuccess(__('%1 coupon(s) have been generated.', $generated));
                    $this->_view->getLayout()->initMessages();
                    $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
