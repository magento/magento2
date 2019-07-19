<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Rule\Model\Action\AbstractAction;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\NewActionHtml
 */
class NewActionHtml extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog implements HttpPostActionInterface
{
    /**
     * New action html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class))
            ->setPrefix('actions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractAction) {
            $formName = $this->getRequest()->getParam('form_namespace');
            $model->setJsFormObject($formName);
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
