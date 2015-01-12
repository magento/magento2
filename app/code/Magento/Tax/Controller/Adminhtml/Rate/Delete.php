<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Delete Rate and Data
     *
     * @return void
     */
    public function execute()
    {
        if ($rateId = $this->getRequest()->getParam('rate')) {
            try {
                $this->_taxRateRepository->deleteById($rateId);

                $this->messageManager->addSuccess(__('The tax rate has been deleted.'));
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(
                    __('Something went wrong deleting this rate because of an incorrect rate ID.')
                );
                $this->getResponse()->setRedirect($this->getUrl('tax/*/'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong deleting this rate.'));
            }

            if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
                $this->getResponse()->setRedirect($referer);
            } else {
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            }
        }
    }
}
