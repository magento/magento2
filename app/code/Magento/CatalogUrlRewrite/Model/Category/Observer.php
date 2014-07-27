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
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\CatalogUrlRewrite\Helper\Data as CatalogUrlRewriteHelper;
use Magento\CatalogUrlRewrite\Service\V1\CategoryUrlGeneratorInterface;
use Magento\CatalogUrlRewrite\Service\V1\ProductUrlGeneratorInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRedirect\Service\V1\UrlSaveInterface;

class Observer
{
    /**
     * @var CategoryUrlGeneratorInterface
     */
    protected $categoryUrlGenerator;

    /**
     * @var CategoryUrlGeneratorInterface
     */
    protected $productUrlGenerator;

    /**
     * @var UrlSaveInterface
     */
    protected $urlSave;

    /**
     * @var CatalogUrlRewriteHelper
     */
    protected $catalogUrlRewriteHelper;

    /**
     * @param CategoryUrlGeneratorInterface $categoryUrlGenerator
     * @param ProductUrlGeneratorInterface $productUrlGenerator
     * @param UrlSaveInterface $urlSave
     * @param CatalogUrlRewriteHelper $catalogUrlRewriteHelper
     */
    public function __construct(
        CategoryUrlGeneratorInterface $categoryUrlGenerator,
        ProductUrlGeneratorInterface $productUrlGenerator,
        UrlSaveInterface $urlSave,
        CatalogUrlRewriteHelper $catalogUrlRewriteHelper
    ) {
        $this->categoryUrlGenerator = $categoryUrlGenerator;
        $this->productUrlGenerator = $productUrlGenerator;
        $this->urlSave = $urlSave;
        $this->catalogUrlRewriteHelper = $catalogUrlRewriteHelper;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function processUrlRewriteSaving(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();

        if (!$this->catalogUrlRewriteHelper->isRootCategory($category)
            && (!$category->getData('url_key') || $category->getOrigData('url_key') != $category->getData('url_key'))
        ) {
            $this->urlSave->save($this->categoryUrlGenerator->generate($category));

            $products = $category->getProductCollection()
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');

            foreach ($products as $product) {
                $product->setData('save_rewrites_history', $category->getData('save_rewrites_history'));

                $this->urlSave->save($this->productUrlGenerator->generateWithChangedCategories(
                    $product,
                    [$category->getId() => $category]
                ));
            }
        }
    }
}
