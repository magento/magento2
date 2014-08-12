<?php
/**
 * Update items statistics and remove the items which are not available in Google Content
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
     * {@inheritdoc}
     */
    public function execute()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return;
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        $itemIds = $this->getRequest()->getParam('item');

        try {
            $flag->lock();
            $operation = $this->operation;
            $this->_objectManager->create(
                'Magento\GoogleShopping\Model\MassOperations'
            )->setFlag(
                $flag
            )->$operation(
                $itemIds
            );
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            // Google requires CAPTCHA for login
            $this->messageManager->addError(__($e->getMessage()));
            $flag->unlock();
            $this->_redirectToCaptcha($e);
            return;
        } catch (\Exception $e) {
            $flag->unlock();
            $this->notifier->addMajor(
                __('An error has occurred while deleting products from google shopping account.'),
                __(
                    'One or more products were not deleted from google shopping account. Refer to the log file for details.'
                )
            );
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            return;
        }

        $flag->unlock();
    }
}
