<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;

class MassDisable extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Mass action for cache disabling
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $types = $this->getRequest()->getParam('types');
        $updatedTypes = 0;
        if (!is_array($types)) {
            $types = [];
        }
        $this->_validateTypes($types);
        foreach ($types as $code) {
            if ($this->_cacheState->isEnabled($code)) {
                $this->_cacheState->setEnabled($code, false);
                $updatedTypes++;
            }
            $this->_cacheTypeList->cleanType($code);
        }
        if ($updatedTypes > 0) {
            $this->_cacheState->persist();
            $this->messageManager->addSuccess(__("%1 cache type(s) disabled.", $updatedTypes));
        }

        return $this->getDefaultResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
