<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class Validate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var \Magento\Catalog\Model\Product\Validator
     */
    protected $productValidator;

    /**
     * @var \Magento\Framework\Controller\Result\JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Catalog\Model\Product\Validator $productValidator
     * @param \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Catalog\Model\Product\Validator $productValidator,
        \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->_dateFilter = $dateFilter;
        $this->productValidator = $productValidator;
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Validate product
     *
     * @return \Magento\Framework\Controller\Result\JSON
     */
    public function execute()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product');

            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $product->setData('_edit_mode', true);
            $storeId = $this->getRequest()->getParam('store');
            if ($storeId) {
                $product->setStoreId($storeId);
            }
            $setId = $this->getRequest()->getParam('set');
            if ($setId) {
                $product->setAttributeSetId($setId);
            }
            $typeId = $this->getRequest()->getParam('type');
            if ($typeId) {
                $product->setTypeId($typeId);
            }
            $productId = $this->getRequest()->getParam('id');
            if ($productId) {
                $product->load($productId);
            }

            $dateFieldFilters = [];
            $attributes = $product->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $productData) && $productData[$attrKey] != '') {
                        $dateFieldFilters[$attrKey] = $this->_dateFilter;
                    }
                }
            }
            $inputFilter = new \Zend_Filter_Input($dateFieldFilters, [], $productData);
            $productData = $inputFilter->getUnescaped();
            $product->addData($productData);

            /* set restrictions for date ranges */
            $resource = $product->getResource();
            $resource->getAttribute('special_from_date')->setMaxValue($product->getSpecialToDate());
            $resource->getAttribute('news_from_date')->setMaxValue($product->getNewsToDate());
            $resource->getAttribute('custom_design_from')->setMaxValue($product->getCustomDesignTo());

            $this->productValidator->validate($product, $this->getRequest(), $response);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Framework\Model\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
