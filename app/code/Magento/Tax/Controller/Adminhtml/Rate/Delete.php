<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Exception;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Tax\Controller\Adminhtml\Rate;

class Delete extends Rate implements HttpPostActionInterface
{
    /**
     * Delete Rate and Data
     *
     * @return ResultRedirect|void
     */
    public function execute()
    {
        if ($rateId = $this->getRequest()->getParam('rate')) {
            /** @var ResultRedirect $resultRedirect */
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
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
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
