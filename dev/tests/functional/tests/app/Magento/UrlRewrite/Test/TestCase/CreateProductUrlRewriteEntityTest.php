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

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\TestCase\Injectable;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;

/**
 * Test Creation for Product URL Rewrites Entity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create custom storeView
 * 2. Create simple product
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing->Url Redirects
 * 3. Click "Add URL Rewrite" button
 * 4. Select "For Product" from  "Create URL Rewrite:" dropdown
 * 5. Select created early product
 * 6. Click "Skip Category Selection" button
 * 7. Fill data according to dataSet
 * 8. Perform all assertions
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId MAGETWO-25150
 */
class CreateProductUrlRewriteEntityTest extends Injectable
{
    /**
     * Url rewrite index page
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Prepare pages
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @return void
     */
    public function __inject(UrlRewriteIndex $urlRewriteIndex, UrlRewriteEdit $urlRewriteEdit)
    {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
    }

    /**
     * Create product URL Rewrite
     *
     * @param CatalogProductSimple $product
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function testProductUrlRewrite(CatalogProductSimple $product, UrlRewrite $urlRewrite)
    {
        //Precondition
        $product->persist();
        $filter = ['id' => $product->getId()];
        //Steps
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getPageActionsBlock()->addNew();
        $this->urlRewriteEdit->getUrlRewriteTypeSelectorBlock()->selectType('For product');
        $this->urlRewriteEdit->getProductGridBlock()->searchAndOpen($filter);
        $this->urlRewriteEdit->getTreeBlock()->skipCategorySelection();
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
