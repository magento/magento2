<?php
/**
 * Update items statistics and remove the items which are not available in Google Content
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

class Refresh extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * Name of the operation to execute
     *
     * @var string
     */
    protected $operation = 'synchronizeItems';

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return $this->emptyResult();
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
                __('An error has occurred while deleting products from google shopping account.'),
                __(
                    'One or more products were not deleted from google shopping account. Refer to the log file for details.'
                )
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->emptyResult();
        }

        $flag->unlock();
        return $this->emptyResult();
    }
}
