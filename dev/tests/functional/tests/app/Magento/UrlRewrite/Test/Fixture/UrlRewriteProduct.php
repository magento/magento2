<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\UrlRewrite\Test\Fixture;

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;

/**
 * Class UrlRewriteProduct
 * URL rewrite product fixture
 */
class UrlRewriteProduct extends DataFixture
{
    /**
     * Product for which URL rewrite is created
     *
     * @var ProductFixture
     */
    protected $product;

    /**
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['rewritten_product_request_path'] = array($this, 'getRewrittenRequestPath');
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoUrlRewriteUrlRewriteProduct($this->_dataConfig, $this->_data);

        $this->product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $this->product->switchData('simple');
        $this->product->persist();
    }

    /**
     * Retrieve rewritten request path
     *
     * @return string
     */
    public function getRewrittenRequestPath()
    {
        $categoryPath = str_replace(' ', '-', strtolower($this->product->getCategoryName()));
        return $categoryPath . '/' . $this->product->getUrlKey() . '-custom-redirect.html';
    }

    /**
     * Retrieve original request path
     *
     * @return string
     */
    public function getOriginalRequestPath()
    {
        $categoryPath = str_replace(' ', '-', strtolower($this->product->getCategoryName()));
        return $categoryPath . '/' . $this->product->getUrlKey() . '.html';
    }

    /**
     * Retrieve product SKU
     *
     * @return int
     */
    public function getProductSku()
    {
        return $this->product->getProductSku();
    }

    /**
     * Retrieve category name to which the product is assigned
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->product->getCategoryName();
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
