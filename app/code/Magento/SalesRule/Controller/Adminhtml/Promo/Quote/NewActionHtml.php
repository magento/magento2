<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\SalesRule\Model\Rule;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * New action html action
 */
class NewActionHtml extends NewHtml
{
    /**
     * @var string
     */
    protected string $typeChecked = 'Magento\Rule\Model\Condition\AbstractCondition';

    /**
     * New action html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode(
            '|',
            str_replace('-', '/', $this->getRequest()->getParam('type', ''))
        );
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type);
        if ($this->verifyClassName($model)) {
            $model->setId($id)
                ->setType($type)
                ->setRule($this->_objectManager->create(Rule::class))
                ->setPrefix('actions');
            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            $model->setJsFormObject($formName);
            $model->setFormName($formName);
            $this->setJsFormObject($model);
            $html = $model->asHtmlRecursive();
        } else {
            $html = $this->getErrorJson();
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Set jsFormObject for the model object
     *
     * @return void
     * @param AbstractCondition $model
     */
    private function setJsFormObject(AbstractCondition $model): void
    {
        $requestJsFormName = $this->getRequest()->getParam('form');
        $actualJsFormName = $this->getJsFormObjectName($model->getFormName());
        if ($requestJsFormName === $actualJsFormName) { //new
            $model->setJsFormObject($actualJsFormName);
        } else { //edit
            $model->setJsFormObject($requestJsFormName);
        }
    }

    /**
     * Get jsFormObject name
     *
     * @param string $formName
     * @return string
     */
    private function getJsFormObjectName(string $formName): string
    {
        return $formName . 'rule_actions_fieldset_';
    }
}
