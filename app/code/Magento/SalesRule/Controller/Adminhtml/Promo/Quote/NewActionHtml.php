<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\NewActionHtml
 *
 * @since 2.0.0
 */
class NewActionHtml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * New action html action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class)
        )->setPrefix(
            'actions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
