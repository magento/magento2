<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Model\Rule\Job;

class ApplyRules extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * Apply all active catalog price rules
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $errorMessage = __('We can\'t apply the rules.');
        /** @var Job $ruleJob */
        $ruleJob = $this->_objectManager->get('Magento\CatalogRule\Model\Rule\Job');
        $ruleJob->applyAll();

        if ($ruleJob->hasSuccess()) {
            $this->messageManager->addSuccess($ruleJob->getSuccess());
            $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(0)->save();
        } elseif ($ruleJob->hasError()) {
            $this->messageManager->addError($errorMessage . ' ' . $ruleJob->getError());
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
        return $resultRedirect->setPath('catalog_rule/*');
    }
}
