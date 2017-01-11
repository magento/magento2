<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create default simple product.
 * 2. Create custom url rewrite.
 *
 * Steps:
 * 1. Login to backend as Admin.
 * 2. Go to the Marketing > SEO & Search->URL Redirects.
 * 3. Search and open created URL Redirect.
 * 4. Fill data according to data set.
 * 5. Save Redirect.
 * 6. Perform all assertions.
 *
 * @group URL_Rewrites
 * @ZephyrId MAGETWO-25784
 */
class UpdateCustomUrlRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
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
     * Inject pages.
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
     * Update custom URL Rewrite.
     *
     * @param UrlRewrite $initialRewrite
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(UrlRewrite $initialRewrite, UrlRewrite $urlRewrite)
    {
        //Precondition
        $initialRewrite->persist();

        //Steps
        $this->urlRewriteIndex->open();
        $filter = ['request_path' => $initialRewrite->getRequestPath()];
        $replaceData = $this->getReplaceData($urlRewrite);
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite, null, $replaceData);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }

    /**
     * Prepare data for replace.
     *
     * @param UrlRewrite $initialRewrite
     * @return array
     */
    protected function getReplaceData(UrlRewrite $initialRewrite)
    {
        $replaceData = [];
        $entity = $initialRewrite->getDataFieldConfig('target_path')['source']->getEntity();

        if ($entity) {
            $replaceData['target_path'] = ['name' => 'sku', 'value' => $entity->getSku()];
        }

        return $replaceData;
    }
}
