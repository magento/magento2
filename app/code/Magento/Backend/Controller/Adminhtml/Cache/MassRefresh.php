<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Cache\MassRefresh
 *
 * @since 2.0.0
 */
class MassRefresh extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Mass action for cache refresh
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $types = $this->getRequest()->getParam('types');
            $updatedTypes = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validateTypes($types);
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
                $updatedTypes++;
            }
            if ($updatedTypes > 0) {
                $this->messageManager->addSuccess(__("%1 cache type(s) refreshed.", $updatedTypes));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while refreshing cache.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*');
    }
}
