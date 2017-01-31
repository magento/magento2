<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\StoreDelete;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreNew;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create custom store view.
 * 2. Create CMS Page.
 * 3. Create CMS Page URL Redirect.
 *
 * Steps:
 * 1. Login to backend as Admin.
 * 2. Go to the Marketing-> SEO & Search->URL Redirects.
 * 3. Search and open created URL Redirect.
 * 4. Fill data according to data set.
 * 5. Save Redirect.
 * 6. Perform all assertions.
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId MAGETWO-26173
 */
class UpdateCmsPageRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Url rewrite index page.
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page.
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Page StoreIndex.
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page StoreNew.
     *
     * @var StoreNew
     */
    protected $storeNew;

    /**
     * Page StoreDelete.
     *
     * @var StoreDelete
     */
    protected $storeDelete;

    /**
     * Store Name.
     *
     * @var string
     */
    protected $storeName;

    /**
     * Skipped stores for tearDown.
     *
     * @var array
     */
    protected $skippedStores = [
        'Main Website/Main Website Store/Default Store View',
    ];

    /**
     * Inject pages.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @param StoreIndex $storeIndex
     * @param StoreNew $storeNew
     * @param StoreDelete $storeDelete
     * @return void
     */
    public function __inject(
        UrlRewriteIndex $urlRewriteIndex,
        UrlRewriteEdit $urlRewriteEdit,
        StoreIndex $storeIndex,
        StoreNew $storeNew,
        StoreDelete $storeDelete
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
        $this->storeIndex = $storeIndex;
        $this->storeNew = $storeNew;
        $this->storeDelete = $storeDelete;
    }

    /**
     * Update CMS page rewrites.
     *
     * @param UrlRewrite $urlRewrite
     * @param UrlRewrite $cmsPageRewrite
     * @return array
     */
    public function test(UrlRewrite $urlRewrite, UrlRewrite $cmsPageRewrite)
    {
        // Preconditions
        $cmsPageRewrite->persist();

        // Steps
        $this->urlRewriteIndex->open();
        $this->storeName = $urlRewrite->getStoreId();
        $filter = ['request_path' => $cmsPageRewrite->getRequestPath()];
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();

        return ['cmsPage' => $cmsPageRewrite->getDataFieldConfig('target_path')['source']->getEntity()];
    }

    /**
     * Delete Store after test.
     *
     * @return void|null
     */
    public function tearDown()
    {
        if (in_array($this->storeName, $this->skippedStores)) {
            return;
        }
        $storeName = explode("/", $this->storeName);
        $filter['store_title'] = end($storeName);
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpen($filter);
        $this->storeNew->getFormPageActions()->delete();
        $this->storeDelete->getStoreForm()->fillForm(['create_backup' => 'No']);
        $this->storeDelete->getFormPageActions()->delete();
    }
}
