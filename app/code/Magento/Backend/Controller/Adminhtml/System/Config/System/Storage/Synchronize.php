<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Config\System\Storage;

class Synchronize extends \Magento\Backend\Controller\Adminhtml\System\Config\System\Storage
{
    /**
     * Synchronize action between storages
     *
     * @return void
     */
    public function execute()
    {
        session_write_close();

        $requestStorage = $this->getRequest()->getParam('storage');
        $requestConnection = $this->getRequest()->getParam('connection');
        if (!isset($requestStorage)) {
            return;
        }

        $flag = $this->_getSyncFlag();
        if ($flag &&
            $flag->getState() == \Magento\Core\Model\File\Storage\Flag::STATE_RUNNING &&
            $flag->getLastUpdate() &&
            time() <= strtotime(
                $flag->getLastUpdate()
            ) + \Magento\Core\Model\File\Storage\Flag::FLAG_TTL
        ) {
            return;
        }

        $flag->setState(\Magento\Core\Model\File\Storage\Flag::STATE_RUNNING)->setFlagData([])->save();

        $storage = ['type' => $requestStorage];
        if (isset($requestConnection) && !empty($requestConnection)) {
            $storage['connection'] = $requestConnection;
        }

        try {
            $this->_getSyncSingleton()->synchronize($storage);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $flag->passError($e);
        }

        $flag->setState(\Magento\Core\Model\File\Storage\Flag::STATE_FINISHED)->save();
    }
}
