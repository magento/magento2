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
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Controller\Adminhtml;

use Magento\App\ResponseInterface;

/**
 * Newsletter subscribers controller
 */
class Subscriber extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Newsletter subscribers page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Newsletter Subscribers'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_subscriber');

        $this->_addBreadcrumb(__('Newsletter'), __('Newsletter'));
        $this->_addBreadcrumb(__('Subscribers'), __('Subscribers'));

        $this->_view->renderLayout();
    }

    /**
     * Managing newsletter grid
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Export subscribers grid to CSV format
     *
     * @return ResponseInterface
     */
    public function exportCsvAction()
    {
        $this->_view->loadLayout();
        $fileName = 'subscribers.csv';
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.newslettrer.subscriber.grid', 'grid.export');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            \Magento\App\Filesystem::VAR_DIR
        );
    }

    /**
     * Export subscribers grid to XML format
     *
     * @return ResponseInterface
     */
    public function exportXmlAction()
    {
        $this->_view->loadLayout();
        $fileName = 'subscribers.xml';
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.newslettrer.subscriber.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            \Magento\App\Filesystem::VAR_DIR
        );
    }

    /**
     * Unsubscribe one or more subscribers action
     *
     * @return void
     */
    public function massUnsubscribeAction()
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->_objectManager->create(
                        'Magento\Newsletter\Model\Subscriber'
                    )->load(
                        $subscriberId
                    );
                    $subscriber->unsubscribe();
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Delete one or more subscribers action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->_objectManager->create(
                        'Magento\Newsletter\Model\Subscriber'
                    )->load(
                        $subscriberId
                    );
                    $subscriber->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::subscriber');
    }
}
