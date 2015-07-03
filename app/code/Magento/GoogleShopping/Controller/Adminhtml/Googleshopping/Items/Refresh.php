<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

/**
 * Update items statistics and remove the items which are not available in Google Content
 */
class Refresh extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * Name of the operation to execute
     *
     * @var string
     */
    protected $operation = 'synchronizeItems';

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        $itemIds = $this->getRequest()->getParam('item');

        try {
            $flag->lock();
            $operation = $this->operation;
            $this->_objectManager->create('Magento\GoogleShopping\Model\MassOperations')
                ->setFlag($flag)
                ->$operation($itemIds);
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            // Google requires CAPTCHA for login
            $this->messageManager->addError(__($e->getMessage()));
            $flag->unlock();
            return $this->_redirectToCaptcha($e);
        } catch (\Exception $e) {
            $flag->unlock();
            $this->notifier->addMajor(
                __('Something went wrong while deleting products from the Google shopping account.'),
                __(
                    'One or more products were not deleted from the Google shopping account. Please review the log file for details.'
                )
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        }

        $flag->unlock();
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
    }
}
