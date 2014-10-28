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
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

class CatalogRule
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_processor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $processor
    ) {
        $this->_processor = $processor;
    }

    /**
     * Invalidate price indexer
     *
     * @param \Magento\CatalogRule\Model\Rule $subject
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterApplyAll(\Magento\CatalogRule\Model\Rule $subject)
    {
        $this->_processor->markIndexerAsInvalid();
    }

    /**
     * Reindex price for affected product
     *
     * @param \Magento\CatalogRule\Model\Rule $subject
     * @param callable $proceed
     * @param int|\Magento\Catalog\Model\Product $product
     * @param null|array $websiteIds
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApplyToProduct(
        \Magento\CatalogRule\Model\Rule $subject,
        \Closure $proceed,
        $product,
        $websiteIds = null
    ) {
        $proceed($product, $websiteIds);
        $this->_reindexProduct($product);
    }

    /**
     * Reindex price for affected product
     *
     * @param \Magento\CatalogRule\Model\Rule $subject
     * @param callable $proceed
     * @param int|\Magento\Catalog\Model\Product $product
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApplyAllRulesToProduct(
        \Magento\CatalogRule\Model\Rule $subject,
        \Closure $proceed,
        $product
    ) {
        $proceed($product);
        $this->_reindexProduct($product);
    }

    /**
     * Reindex product price
     *
     * @param int|\Magento\Catalog\Model\Product $product
     *
     * @return void
     */
    protected function _reindexProduct($product)
    {
        $productId = is_numeric($product) ? $product : $product->getId();
        $this->_processor->reindexRow($productId);
    }
}
