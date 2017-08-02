<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Statistics\RefreshRecent
 *
 * @since 2.0.0
 */
class RefreshRecent extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * Refresh statistics for last 25 hours
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            /** @var \DateTime $currentDate */
            $currentDate = $this->_objectManager->get(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
            )->date();
            $date = $currentDate->modify('-25 hours');
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate($date);
            }
            $this->messageManager->addSuccess(__('Recent statistics have been updated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh recent statistics.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        if ($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl('*/*'));
        }
    }
}
