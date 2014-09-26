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

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Mtf\TestCase\Injectable;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Test Creation for Delete Category URL Rewrites Entity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create category
 * 2. Create custom category UrlRewrite
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing->URL Rewrites
 * 3. Search and open created URL Rewrite
 * 4. Delete URL Rewrite
 * 5. Perform all assertions
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId MAGETWO-25086
 */
class DeleteCategoryUrlRewriteEntityTest extends Injectable
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
     * Inject pages
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
     * Delete category Url Rewrite
     *
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function testDeleteCategoryUrlRewrite(UrlRewrite $urlRewrite)
    {
        //Precondition
        $urlRewrite->persist();
        //Steps
        $this->urlRewriteIndex->open();
        if ($urlRewrite->getRequestPath()) {
            $filter = ['request_path' => $urlRewrite->getRequestPath()];
        } else {
            $filter = ['id_path' => $urlRewrite->getIdPath()];
        }
        $this->urlRewriteIndex->getUrlRewriteGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getPageMainActions()->delete();
    }
}
