<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Tax\Controller\Adminhtml\Rate\Delete
 *
 */
class Delete extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Delete Rate and Data
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|void
     */
    public function execute()
    {
        if ($rateId = $this->getRequest()->getParam('rate')) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            try {
                $this->_taxRateRepository->deleteById($rateId);

                $this->messageManager->addSuccess(__('You deleted the tax rate.'));
                return $resultRedirect->setPath("*/*/");
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this rate because of an incorrect rate ID.')
                );
                return $resultRedirect->setPath("tax/*/");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong deleting this rate.'));
            }

            if ($this->getRequest()->getServer('HTTP_REFERER')) {
                $resultRedirect->setRefererUrl();
            } else {
                $resultRedirect->setPath("*/*/");
            }
            return $resultRedirect;
        }
    }
}
