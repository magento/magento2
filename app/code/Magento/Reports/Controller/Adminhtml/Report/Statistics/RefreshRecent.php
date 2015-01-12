<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;


class RefreshRecent extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * Refresh statistics for last 25 hours
     *
     * @return void
     */
    public function execute()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            /** @var \Magento\Framework\Stdlib\DateTime\DateInterface $currentDate */
            $currentDate = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')->date();
            $date = $currentDate->subHour(25);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate($date);
            }
            $this->messageManager->addSuccess(__('Recent statistics have been updated.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh recent statistics.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        if ($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl('*/*'));
        }
    }
}
