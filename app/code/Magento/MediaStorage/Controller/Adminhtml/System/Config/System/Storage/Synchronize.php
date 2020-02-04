<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Controller\Adminhtml\System\Config\System\Storage;

class Synchronize extends \Magento\MediaStorage\Controller\Adminhtml\System\Config\System\Storage
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
            $flag->getState() == \Magento\MediaStorage\Model\File\Storage\Flag::STATE_RUNNING &&
            $flag->getLastUpdate() &&
            time() <= strtotime(
                $flag->getLastUpdate()
            ) + \Magento\MediaStorage\Model\File\Storage\Flag::FLAG_TTL
        ) {
            return;
        }

        $flag->setState(\Magento\MediaStorage\Model\File\Storage\Flag::STATE_RUNNING)->setFlagData([])->save();

        $storage = ['type' => $requestStorage];
        if (isset($requestConnection) && !empty($requestConnection)) {
            $storage['connection'] = $requestConnection;
        }

        try {
            $this->_getSyncSingleton()->synchronize($storage);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $flag->passError($e);
        }

        $flag->setState(\Magento\MediaStorage\Model\File\Storage\Flag::STATE_FINISHED)->save();
    }
}
