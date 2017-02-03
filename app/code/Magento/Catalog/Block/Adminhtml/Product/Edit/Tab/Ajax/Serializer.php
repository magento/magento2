<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax;

use Magento\Framework\View\Element\Template;

/**
 * Class Serializer
 * @package Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax
 * @deprecated
 */
class Serializer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     * @param array $data
     * @throws \RuntimeException
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    /**
     * @return $this
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('catalog/product/edit/serializer.phtml');
        return $this;
    }

    /**
     * @return string
     * @deprecated
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
