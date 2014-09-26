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
 * Test Creation for CreateCustomUrlRewriteEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create CMS Page
 * 2. Create subcategory
 * 3. Create simple product
 *
 * Steps:
 * 1. Login to backend as Admin
 * 2. Go to the Marketing-> SEO & Search->URL Rewrites
 * 3. Click "Add Url Rewrite" button
 * 4. Select "Custom" in Create URL Rewrite dropdown
 * 5. Fill data according to data set
 * 6. Save Rewrite
 * 7. Perform all assertions
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId MAGETWO-25474
 */
class CreateCustomUrlRewriteEntityTest extends Injectable
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
     * Create custom URL Rewrite
     *
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(UrlRewrite $urlRewrite)
    {
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getPageActionsBlock()->addNew();
        $this->urlRewriteEdit->getUrlRewriteTypeSelectorBlock()->selectType('Custom');
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
