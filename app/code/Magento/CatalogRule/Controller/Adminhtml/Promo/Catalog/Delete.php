<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Framework\Model\Exception;

class Delete extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Magento\CatalogRule\Model\Rule $model */
                $model = $this->_objectManager->create('Magento\CatalogRule\Model\Rule');
                $model->load($id);
                $model->delete();
                $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(1)->save();
                $this->messageManager->addSuccess(__('The rule has been deleted.'));
                $this->_redirect('catalog_rule/*/');
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting the rule. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->_redirect('catalog_rule/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('Unable to find a rule to delete.'));
        $this->_redirect('catalog_rule/*/');
    }
}
