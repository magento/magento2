<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Model\Rule\Job;

class ApplyRules extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * Apply all active catalog price rules
     *
     * @return void
     */
    public function execute()
    {
        $errorMessage = __('Unable to apply rules.');
        try {
            /** @var Job $ruleJob */
            $ruleJob = $this->_objectManager->get('Magento\CatalogRule\Model\Rule\Job');
            $ruleJob->applyAll();

            if ($ruleJob->hasSuccess()) {
                $this->messageManager->addSuccess($ruleJob->getSuccess());
                $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(0)->save();
            } elseif ($ruleJob->hasError()) {
                $this->messageManager->addError($errorMessage . ' ' . $ruleJob->getError());
            }
        } catch (\Exception $e) {
            $this->_objectManager->create('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError($errorMessage);
        }
        $this->_redirect('catalog_rule/*');
    }
}
