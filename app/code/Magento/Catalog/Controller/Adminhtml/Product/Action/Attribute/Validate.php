<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate
 *
 * @since 2.0.0
 */
class Validate extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $attributeHelper);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Attributes validation action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $response = $this->_objectManager->create(\Magento\Framework\DataObject::class);
        $response->setError(false);
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $data = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);

        try {
            if ($attributesData) {
                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_objectManager->get(\Magento\Eav\Model\Config::class)
                        ->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    $data->setData($attributeCode, $value);
                    $attribute->getBackend()->validate($data);
                }
            }
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
