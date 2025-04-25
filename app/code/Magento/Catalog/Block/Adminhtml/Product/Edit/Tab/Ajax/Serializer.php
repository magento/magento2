<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax;

use Magento\Framework\View\Element\Template;

class Serializer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $data
     * @throws \RuntimeException
     */
    public function __construct(
        Template\Context $context,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Serializer Constructor
     *
     * @return $this
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Catalog::catalog/product/edit/serializer.phtml');
        return $this;
    }

    /**
     * Method to get Products JSON data
     *
     * @return string
     * @deprecated 102.0.0
     * @see Updated deprecation doc annotations
     */
    public function getProductsJSON()
    {
        $result = [];
        if ($this->getProducts()) {
            $isEntityId = $this->getIsEntityId();
            foreach ($this->getProducts() as $product) {
                $id = $isEntityId ? $product->getEntityId() : $product->getId();
                $result[$id] = $product->toArray(['qty', 'position']);
            }
        }
        return $result ? $this->serializer->serialize($result) : '{}';
    }
}
