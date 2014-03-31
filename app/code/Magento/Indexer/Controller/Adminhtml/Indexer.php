<?php
/**
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
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Indexer\Controller\Adminhtml;

class Indexer extends \Magento\Backend\App\Action
{
    /**
     * Display processes grid action
     *
     * @return void
     */
    public function listAction()
    {
        $this->_title->add(__('New Index Management'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Indexer::system_index');
        $this->_view->renderLayout();
    }

    /**
     * Turn mview off for the given indexers
     *
     * @return void
     */
    public function massOnTheFlyAction()
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addError(__('Please select indexers.'));
        } else {
            try {
                foreach ($indexerIds as $indexer_id) {
                    /** @var \Magento\Indexer\Model\IndexerInterface $model */
                    $model = $this->_objectManager->create(
                        'Magento\Indexer\Model\IndexerInterface'
                    )->load(
                        $indexer_id
                    );
                    $model->setScheduled(false);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 indexer(s) have been turned Update on Save mode on.', count($indexerIds))
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't change indexer(s)' mode because of an error.")
                );
            }
        }
        $this->_redirect('*/*/list');
    }

    /**
     * Turn mview on for the given indexers
     *
     * @return void
     */
    public function massChangelogAction()
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addError(__('Please select indexers.'));
        } else {
            try {
                foreach ($indexerIds as $indexer_id) {
                    /** @var \Magento\Indexer\Model\IndexerInterface $model */
                    $model = $this->_objectManager->create(
                        'Magento\Indexer\Model\IndexerInterface'
                    )->load(
                        $indexer_id
                    );
                    $model->setScheduled(true);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 indexer(s) have been turned Update by Schedule mode on.', count($indexerIds))
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't change indexer(s)' mode because of an error.")
                );
            }
        }
        $this->_redirect('*/*/list');
    }

    /**
     * Check ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->_request->getActionName()) {
            case 'list':
                return $this->_authorization->isAllowed('Magento_Indexer::index');
            case 'massOnTheFly':
            case 'massChangelog':
                return $this->_authorization->isAllowed('Magento_Indexer::changeMode');
        }
        return false;
    }
}
