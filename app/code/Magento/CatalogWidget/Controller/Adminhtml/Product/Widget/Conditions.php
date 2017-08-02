<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Controller\Adminhtml\Product\Widget;

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class Conditions
 * @since 2.0.0
 */
class Conditions extends \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule
     * @since 2.0.0
     */
    protected $rule;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\CatalogWidget\Model\Rule $rule
    ) {
        $this->rule = $rule;
        parent::__construct($context);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeData = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $className = $typeData[0];

        $model = $this->_objectManager->create($className)
            ->setId($id)
            ->setType($className)
            ->setRule($this->rule)
            ->setPrefix('conditions');

        if (!empty($typeData[1])) {
            $model->setAttribute($typeData[1]);
        }

        $result = '';
        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $result = $model->asHtmlRecursive();
        }
        $this->getResponse()->setBody($result);
    }
}
