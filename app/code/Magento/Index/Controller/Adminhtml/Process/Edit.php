<?php
/**
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
namespace Magento\Index\Controller\Adminhtml\Process;

class Edit extends \Magento\Index\Controller\Adminhtml\Process
{
    /**
     * Process detail and edit action
     *
     * @return void
     */
    public function execute()
    {
        /** @var $process \Magento\Index\Model\Process */
        $process = $this->_initProcess();
        if ($process) {
            $this->_title->add($process->getIndexCode());
            $this->_title->add(__('System'));
            $this->_title->add(__('Index Management'));
            $this->_title->add(__($process->getIndexer()->getName()));

            $this->_coreRegistry->register('current_index_process', $process);
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('Cannot initialize the indexer process.'));
            $this->_redirect('adminhtml/*/list');
        }
    }
}
