<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\CmsIndex as FrontCmsIndex;
use Magento\Cms\Test\Page\CmsPage as FrontCmsPage;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that content of created cms page displays product list.
 */
class AssertCmsPageProductListPrevew extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that content of created cms page display product list.
     *
     * @param CmsPageIndex $cmsIndex
     * @param FrontCmsIndex $frontCmsIndex
     * @param FrontCmsPage $frontCmsPage
     * @param CmsPage $cms
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CmsPageIndex $cmsIndex,
        FrontCmsIndex $frontCmsIndex,
        FrontCmsPage $frontCmsPage,
        CmsPage $cms,
        BrowserInterface $browser
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndPreview($filter);
        $browser->selectWindow();

        \PHPUnit_Framework_Assert::assertNotEmpty(
            $frontCmsPage->getCmsPageBlock()->getProductsGridContent(),
            'No Products in product list.'
        );

        if ($cms->getContentHeading()) {
            \PHPUnit_Framework_Assert::assertEquals(
                $cms->getContentHeading(),
                $frontCmsIndex->getTitleBlock()->getTitle(),
                'Wrong title is displayed.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS Page content has products.';
    }
}
