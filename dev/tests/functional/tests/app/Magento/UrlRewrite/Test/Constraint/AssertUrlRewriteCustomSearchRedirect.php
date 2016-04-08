<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCustomSearchRedirect
 * Assert that product was found on search page
 */
class AssertUrlRewriteCustomSearchRedirect extends AbstractConstraint
{
    /**
     * Assert that created entity was found on search page
     *
     * @param UrlRewrite $initialRewrite
     * @param UrlRewrite $urlRewrite
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function processAssert(
        UrlRewrite $initialRewrite,
        UrlRewrite $urlRewrite,
        BrowserInterface $browser,
        CatalogCategoryView $categoryView
    ) {
        $urlRequestPath = $urlRewrite->hasData('request_path')
            ? $urlRewrite->getRequestPath()
            : $initialRewrite->getRequestPath();
        $browser->open($_ENV['app_frontend_url'] . $urlRequestPath);
        $entity = $urlRewrite->getDataFieldConfig('target_path')['source']->getEntity();

        \PHPUnit_Framework_Assert::assertTrue(
            $categoryView->getListProductBlock()->getProductItem($entity)->isVisible(),
            "Created entity '{$entity->getName()}' isn't found."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is found on search page.';
    }
}
