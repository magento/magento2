<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Retrieve variables list for WYSIWYG
 *
 * @api
 * @since 100.0.2
 */
class WysiwygPlugin extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Variable::variable';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Email\Model\Source\Variables
     */
    private $storesVariables;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * WysiwygPlugin constructor.
     *
     * @param Action\Context $context
     * @param CollectionFactory|null $collectionFactory
     * @param Variables|null $storesVariables
     * @param JsonFactory|null $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Email\Model\Source\Variables $storesVariables,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->storesVariables = $storesVariables ?: ObjectManager::getInstance()->get(Variables::class);
        $this->resultJsonFactory = $resultJsonFactory ?: ObjectManager::getInstance()->get(JsonFactory::class);
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
                'variable_type' => \Magento\Variable\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
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
        $customVariables = $this->_objectManager->create(\Magento\Variable\Model\Variable::class)
            ->getVariablesOptionArray(true);
        $storeContactVariabls = $this->_objectManager->create(
            \Magento\Variable\Model\Source\Variables::class
        )->toOptionArray(
            true
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([$storeContactVariabls, $customVariables]);
    }
}
