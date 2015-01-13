<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * JSON products custom options
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Options;

use Magento\Store\Model\Store;

class Ajax extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $registry;
        $this->_coreData = $coreData;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return product custom options in JSON format
     *
     * @return string
     */
    protected function _toHtml()
    {
        $results = [];
        /** @var $optionsBlock \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option */
        $optionsBlock = $this->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option'
        )->setIgnoreCaching(
            true
        );

        $products = $this->_coreRegistry->registry('import_option_products');
        if (is_array($products)) {
            foreach ($products as $productId) {
                $product = $this->_productFactory->create();
                $product->setStoreId($this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID));
                $product->load((int)$productId);
                if (!$product->getId()) {
                    continue;
                }

                $optionsBlock->setProduct($product);
                $results = array_merge($results, $optionsBlock->getOptionValues());
            }
        }

        $output = [];
        foreach ($results as $resultObject) {
            $output[] = $resultObject->getData();
        }

        return $this->_jsonEncoder->encode($output);
    }
}
