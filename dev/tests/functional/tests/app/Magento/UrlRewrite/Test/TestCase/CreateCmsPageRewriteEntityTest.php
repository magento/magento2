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

use Magento\UrlRewrite\Test\Page\Adminhtml\EditCmsPage;
use Mtf\TestCase\Injectable;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlrewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlrewriteIndex;

/**
 * Test Creation for CreateCmsPageRewriteEntity
 *
 * Test Flow:
 *
 * Preconditions
 * 1. Create CMS-Page
 *
 * Steps
 * 1. Login to backend as Admin
 * 2. Go to the Marketing-> SEO & Search->URL Redirects
 * 3. Click "Add Url Rewrite" button
 * 4. Select "For CMS Page" in Create URL Rewrite dropdown
 * 5. Select CMS page from preconditions in grid
 * 6. Fill data according to data set
 * 7. Save Rewrite
 * 8. Perform all assertions
 *
 * @group URL_Rewrites_(PS)
 * @ZephyrId MAGETWO-24847
 */
class CreateCmsPageRewriteEntityTest extends Injectable
{
    /**
     * Url rewrite index page
     *
     * @var UrlrewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page
     *
     * @var UrlrewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Inject pages
     *
     * @param UrlrewriteIndex $urlRewriteIndex
     * @param UrlrewriteEdit $urlRewriteEdit
     * @return void
     */
    public function __inject(
        UrlrewriteIndex $urlRewriteIndex,
        UrlrewriteEdit $urlRewriteEdit
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
    }

    /**
     * Create CMS page rewrites
     *
     * @param CmsPage $cmsPage
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function testCmsPageRewrite(CmsPage $cmsPage, UrlRewrite $urlRewrite)
    {
        //Preconditions
        $cmsPage->persist();
        //Steps
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getPageActionsBlock()->addNew();
        $this->urlRewriteEdit->getUrlRewriteTypeSelectorBlock()->selectType('For CMS page');
        $filter = ['title' => $cmsPage->getTitle()];
        $this->urlRewriteEdit->getCmsGridBlock()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
