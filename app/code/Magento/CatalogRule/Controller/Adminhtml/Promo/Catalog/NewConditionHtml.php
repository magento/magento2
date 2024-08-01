<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog as CatalogAction;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\ConditionInterface;

class NewConditionHtml extends CatalogAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Execute new condition html.
     *
     * @return void
     */
    public function execute()
    {
        $objectId = $this->getRequest()->getParam('id');
        $formNamespace = $this->getRequest()->getParam('form_namespace');
        $types = explode(
            '|',
            str_replace('-', '/', $this->getRequest()->getParam('type', ''))
        );
        $objectType = $types[0];
        $responseBody = '';

        if (class_exists($objectType) && !in_array(ConditionInterface::class, class_implements($objectType))) {
            $this->getResponse()->setBody($responseBody);
            return;
        }

        $conditionModel = $this->_objectManager->create($objectType)
            ->setId($objectId)
            ->setType($objectType)
            ->setRule($this->_objectManager->create(Rule::class))
            ->setPrefix('conditions');

        if (!empty($types[1])) {
            $conditionModel->setAttribute($types[1]);
        }

        if ($conditionModel instanceof AbstractCondition) {
            $conditionModel->setJsFormObject($this->getRequest()->getParam('form'));
            $conditionModel->setFormName($formNamespace);
            $responseBody = $conditionModel->asHtmlRecursive();
        }

        $this->getResponse()->setBody($responseBody);
    }
}
