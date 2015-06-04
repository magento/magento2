<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Mode;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\Mode\ModeInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

class Product implements ModeInterface
{
    const ENTITY_TYPE = 'product';
    const SORT_ORDER = 20;

    protected $productFactory;
    protected $request;
    protected $product;

    public function __construct(
        ProductFactory $productFactory,
        RequestInterface $request
    )
    {
        $this->productFactory = $productFactory;
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('For Product');
    }

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    /**
     * @return string
     */
    //TODO: move block to CatalogUrlRewrite module
    public function getEditBlockClass()
    {
        return 'Magento\CatalogUrlRewrite\Block\Product\Edit';
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return self::SORT_ORDER;
    }

    public function getProduct(UrlRewrite $urlRewrite)
    {
        if (is_null($this->product)) {
            $this->product = $this->productFactory->create();
            $productId = (int)$this->request->getParam($this->getEntityType(), 0);
            if (!$productId && $urlRewrite->getId() && $urlRewrite->getEntityType() === $this->getEntityType()) {
                $productId = $urlRewrite->getEntityId();
            }
            if ($productId) {
                $this->product->load($productId);
            }
        }
        return $this->product;
    }

    public function match(UrlRewrite $urlRewrite)
    {
        return $this->getProduct($urlRewrite)->getId() || $this->request->has($this->getEntityType());
    }
}
