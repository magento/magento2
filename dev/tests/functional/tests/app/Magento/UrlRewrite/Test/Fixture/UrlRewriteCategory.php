<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Fixture;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\System\Config;

/**
 * Class UrlRewriteCategory
 * URL rewrite category fixture
 */
class UrlRewriteCategory extends DataFixture
{
    /**
     * Category for which URL rewrite is created
     *
     * @var CategoryFixture
     */
    protected $category;

    /**
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, array $placeholders = [])
    {
        parent::__construct($configuration, $placeholders);
        $this->_placeholders['rewritten_category_request_path'] = [$this, 'getRewrittenRequestPath'];
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoUrlRewriteUrlRewriteCategory($this->_dataConfig, $this->_data);

        $this->category = Factory::getFixtureFactory()->getMagentoCatalogCategory();
        $this->category->persist();
    }

    /**
     * Retrieve rewritten request path
     *
     * @return string
     */
    public function getRewrittenRequestPath()
    {
        $categoryPath = str_replace(' ', '-', strtolower($this->category->getCategoryName()));
        return $categoryPath . '-custom-redirect.html';
    }

    /**
     * Retrieve original request path
     *
     * @return string
     */
    public function getOriginalRequestPath()
    {
        $categoryPath = str_replace(' ', '-', strtolower($this->category->getCategoryName()));
        return $categoryPath . '.html';
    }

    /**
     * Retrieve category name to which the product is assigned
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category->getCategoryName();
    }

    /**
     * Retrieve URL rewrite type
     *
     * @return string
     */
    public function getUrlRewriteType()
    {
        return $this->getData('url_rewrite_type');
    }

    /**
     * Initialize fixture data
     */
    protected function _initData()
    {
    }
}
