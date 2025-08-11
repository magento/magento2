<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute as AttributeAction;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;

class Validate extends AttributeAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ?\Magento\Eav\Model\Config $eavConfig = null
    ) {
        parent::__construct($context, $attributeHelper);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Eav\Model\Config::class);
    }

    /**
     * Attributes validation action
     *
     * @return \Magento\Framework\Controller\ResultInterface
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
                    $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    $data->setData($attributeCode, $value);
                    $this->updateRestrictionsDateRanges($attribute, $attributeCode, $attributesData);
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
            $this->messageManager->addExceptionMessage(
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

    /**
     * Set restrictions for date ranges
     *
     * @param Attribute $attribute
     * @param string $attributeCode
     * @param array $attributesData
     * @return void
     */
    protected function updateRestrictionsDateRanges(&$attribute, $attributeCode, $attributesData)
    {
        if ($attributeCode == 'special_from_date' && !empty($attributesData['special_to_date'])) {
            $attribute->setMaxValue($attributesData['special_to_date']);
        }
        if ($attributeCode == 'news_from_date' && !empty($attributesData['news_to_date'])) {
            $attribute->setMaxValue($attributesData['news_to_date']);
        }
        if ($attributeCode == 'custom_design_from' && !empty($attributesData['custom_design_to'])) {
            $attribute->setMaxValue($attributesData['custom_design_to']);
        }
    }
}
