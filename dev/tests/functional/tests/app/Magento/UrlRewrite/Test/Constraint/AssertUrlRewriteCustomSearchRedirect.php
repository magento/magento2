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

namespace Magento\UrlRewrite\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Class AssertUrlRewriteCustomSearchRedirect
 * Assert that product was found on search page
 */
class AssertUrlRewriteCustomSearchRedirect extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that created entity was found on search page
     *
     * @param UrlRewrite $initialRewrite
     * @param UrlRewrite $urlRewrite
     * @param Browser $browser
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function processAssert(
        UrlRewrite $initialRewrite,
        UrlRewrite $urlRewrite,
        Browser $browser,
        CatalogCategoryView $categoryView
    ) {
        $urlRequestPath = $urlRewrite->hasData('request_path')
            ? $urlRewrite->getRequestPath()
            : $initialRewrite->getRequestPath();
        $browser->open($_ENV['app_frontend_url'] . $urlRequestPath);
        $entity = $initialRewrite->getDataFieldConfig('id_path')['source']->getEntity()->getName();

        \PHPUnit_Framework_Assert::assertTrue(
            $categoryView->getListProductBlock()->isProductVisible($entity),
            "Created entity '{$entity}' isn't found."
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
