<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Mode;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\Mode\ModeInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\Mode\Product as ProductMode;


class Category implements ModeInterface
{
    const ENTITY_TYPE = 'category';
    const SORT_ORDER = 10;

    protected $categoryFactory;
    protected $request;
    protected $category;

    public function __construct(
        CategoryFactory $categoryFactory,
        RequestInterface $request
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->request = $request;
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('For Category');
    }

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    /**
     * @return string
     */
    public function getEditBlockClass()
    {
        return 'Magento\CatalogUrlRewrite\Block\Category\Edit';
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return self::SORT_ORDER;
    }

    /**
     * @param UrlRewrite $urlRewrite
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory(UrlRewrite $urlRewrite)
    {
        if (is_null($this->category)) {
            $this->category = $this->categoryFactory->create();
            $categoryId = (int)$this->request->getParam($this->getEntityType(), 0);
            if (!$categoryId && $urlRewrite->getId() && $urlRewrite->getEntityType() === $this->getEntityType()) {
                $categoryId = $urlRewrite->getEntityId();
            } elseif (!$categoryId && $urlRewrite->getId() && $urlRewrite->getEntityType() === ProductMode::ENTITY_TYPE) {
                $metadata = $urlRewrite->getMetadata();
                $categoryId = isset($metadata['category_id']) ? $metadata['category_id'] : null;
            }
            if ($categoryId) {
                $this->category->load($categoryId);
            }
        }
        return $this->category;
    }

    /**
     * @param UrlRewrite $urlRewrite
     * @return bool|mixed
     */
    public function match(UrlRewrite $urlRewrite)
    {
        return $this->getCategory($urlRewrite)->getId() || $this->request->has($this->getEntityType());
    }
}
