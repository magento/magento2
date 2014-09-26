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

use Mtf\TestCase\Injectable;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Test Creation for UpdateCustomUrlRewritesEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create default simple product
 * 2. Create custom url rewrite
 *
 * Steps:
 * 1. Login to backend as Admin
 * 2. Go to the Marketing-> SEO & Search->URL Redirects
 * 3. Search and open created URL Redirect
 * 4. Fill data according to data set
 * 5. Save Redirect
 * 6. Perform all assertions
 *
 * @group URL_Rewrites_(PS)
 * @ZephyrId MAGETWO-25784
 */
class UpdateCustomUrlRewriteEntityTest extends Injectable
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
     * Update custom URL Rewrite
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
        $replaceData = $this->getReplaceData($initialRewrite);
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite, null, $replaceData);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }

    /**
     * Prepare data for replace
     *
     * @param UrlRewrite $initialRewrite
     * @return array
     */
    protected function getReplaceData(UrlRewrite $initialRewrite)
    {
        $replaceData = [];
        $entity = $initialRewrite->getDataFieldConfig('id_path')['source']->getEntity();

        if ($entity) {
            $replaceData['target_path'] = ['%name%' => $entity->getName()];
        }

        return $replaceData;
    }
}
