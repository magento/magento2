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
namespace Magento\Catalog\Controller\Product\Compare;

class Clear extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Remove all items from comparison list
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection $items */
        $items = $this->_itemCollectionFactory->create();

        if ($this->_customerSession->isLoggedIn()) {
            $items->setCustomerId($this->_customerSession->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId($this->_customerVisitor->getId());
        }

        try {
            $items->clear();
            $this->messageManager->addSuccess(__('You cleared the comparison list.'));
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  clearing the comparison list.'));
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
