<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Backend\App\Action;
use Magento\Variable\Ui\Component\VariablesDataProvider;

/**
 * Retrieve variables list for WYSIWYG
 *
 * @api
 * @since 100.0.2
 */
class WysiwygPlugin extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Email\Model\Source\Variables
     */
    private $storesVariables;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Email\Model\Source\Variables $storesVariables,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->storesVariables = $storesVariables;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Prepare default variables
     *
     * @return array
     */
    private function getDefaultVariables()
    {
        $variables = [];
        foreach ($this->storesVariables->getData() as $variable) {
            $variables[$variable['value']] = [
                'code' => $variable['value'],
                'variable_name' => $variable['label'],
                'variable_type' => \Magento\Email\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
            ];
        }

        return $variables;
    }

    /**
     * Prepare custom variables
     *
     * @return array
     */
    private function getCustomVariables()
    {
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[$variable['code']] = [
                'code' => $variable['code'],
                'variable_name' => $variable['name'],
                'variable_type' => 'custom'
            ];
        }

        return $variables;
    }

    /**
     * WYSIWYG Plugin Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $variablesData = array_merge(
            $this->getCustomVariables(),
            $this->getDefaultVariables()
        );
//        $customVariables = $this->_objectManager->create(\Magento\Variable\Model\Variable::class)
//            ->getVariablesOptionArray(true);
//        $storeContactVariabls = $this->_objectManager->create(
//            \Magento\Email\Model\Source\Variables::class
//        )->toOptionArray(
//            true
//        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($variablesData);
    }
}
