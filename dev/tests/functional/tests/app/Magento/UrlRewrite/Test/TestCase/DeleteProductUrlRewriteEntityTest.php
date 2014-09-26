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
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteProductUrlRewritesEntity
 *
 * Precondition:
 * 1. Sub category is created.
 * 2. Product is created.
 * 3. Product url rewrites is created.
 *
 * Test Flow:
 * 1. Login to backend.
 * 2. Navigate to MARKETING > URL Rewrites.
 * 3. Click Redirect from grid.
 * 4. Click 'Delete' button.
 * 5. Perform asserts.
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId  MAGETWO-23287
 */
class DeleteProductUrlRewriteEntityTest extends Injectable
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
    public function __inject(
        UrlRewriteIndex $urlRewriteIndex,
        UrlRewriteEdit $urlRewriteEdit
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
    }

    /**
     * Delete product url rewrites entity
     *
     * @param UrlRewrite $productRedirect
     * @return void
     */
    public function testDeleteProductUrlRewrite(UrlRewrite $productRedirect)
    {
        // Precondition
        $productRedirect->persist();
        // Steps
        $this->urlRewriteIndex->open();
        $filter = ['request_path' => $productRedirect->getRequestPath()];
        $this->urlRewriteIndex->getUrlRewriteGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getPageMainActions()->delete();
    }
}
