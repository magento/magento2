<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class NewActionHtml extends NewHtml
{
    protected string $typeChecked = 'Magento\Rule\Model\Action\AbstractAction';

    /**
     * Execute new action html.
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type', '')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type);
        if ($this->verifyClassName($model)) {
            $model->setId($id)
                ->setType($type)
                ->setRule($this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class))
                ->setPrefix('actions');

            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        }else {
            $html = $this->getErrorJson();
        }
        $this->getResponse()->setBody($html);
    }
}
