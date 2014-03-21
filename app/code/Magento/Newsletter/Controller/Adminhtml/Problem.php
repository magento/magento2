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

/**
 * Newsletter subscribers controller
 */
class Problem extends \Magento\Backend\App\Action
{
    /**
     * Newsletter problems report page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Newsletter Problems Report'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->getLayout()->getMessagesBlock()->setMessages($this->messageManager->getMessages(true));
        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_problem');
        $this->_addBreadcrumb(__('Newsletter Problem Reports'), __('Newsletter Problem Reports'));

        $this->_view->renderLayout();
    }

    /**
     * Newsletter problems grid
     *
     * @return void
     */
    public function gridAction()
    {
        if ($this->getRequest()->getParam('_unsubscribe')) {
            $problems = (array)$this->getRequest()->getParam('problem', array());
            if (count($problems) > 0) {
                $collection = $this->_objectManager->create('Magento\Newsletter\Model\Resource\Problem\Collection');
                $collection->addSubscriberInfo()->addFieldToFilter(
                    $collection->getResource()->getIdFieldName(),
                    array('in' => $problems)
                )->load();

                $collection->walk('unsubscribe');
            }

            $this->messageManager->addSuccess(__('We unsubscribed the people you identified.'));
        }

        if ($this->getRequest()->getParam('_delete')) {
            $problems = (array)$this->getRequest()->getParam('problem', array());
            if (count($problems) > 0) {
                $collection = $this->_objectManager->create('Magento\Newsletter\Model\Resource\Problem\Collection');
                $collection->addFieldToFilter(
                    $collection->getResource()->getIdFieldName(),
                    array('in' => $problems)
                )->load();
                $collection->walk('delete');
            }

            $this->messageManager->addSuccess(__('The problems you identified have been deleted.'));
        }
        $this->_view->getLayout()->getMessagesBlock()->setMessages($this->messageManager->getMessages(true));
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::problem');
    }
}
