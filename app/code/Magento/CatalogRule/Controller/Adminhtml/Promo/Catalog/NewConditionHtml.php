<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class NewConditionHtml extends NewHtml implements HttpPostActionInterface, HttpGetActionInterface
{
    protected string $typeChecked = 'Magento\Rule\Model\Condition\AbstractCondition';

    /**
     * Execute new condition html.
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type', '')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type);

        if ($this->verifyClassName($model)) {
            $model->setId($id)
                ->setType($type)
                ->setRule($this->_objectManager->create(Rule::class))
                ->setPrefix('conditions');

            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = $this->getErrorJson();
        }

        $this->getResponse()->setBody($html);
    }
}
