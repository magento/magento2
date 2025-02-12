<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Controller\Adminhtml\Product\Widget;

use Magento\Backend\App\Action\Context;
use Magento\CatalogWidget\Model\Rule;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\CatalogWidget\Controller\Adminhtml\Product\Widget;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Conditions extends Widget
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @param Context $context
     * @param Rule $rule
     */
    public function __construct(
        Context $context,
        Rule $rule
    ) {
        $this->rule = $rule;
        parent::__construct($context);
    }

    /**
     * Product widget conditions action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeData = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type', '')));
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
