<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Constraint\AbstractAssertForm;

class AssertCmsPageWidgetConditions extends AbstractAssertForm
{

    /**
     * Assert that widget conditions are properly saved on CMS page.
     *
     * @param CmsPage $cms
     * @param CmsPageIndex $cmsIndex
     * @param CmsPageNew $cmsPageNew
     * @param string $conditions
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        CmsPageIndex $cmsIndex,
        CmsPageNew $cmsPageNew,
        $conditions
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndOpen($filter);

        $cmsPageNew->getPageForm()->openTab('content');
        $content = $cmsPageNew->getPageForm()->getTab('content')->getContent();
        \PHPUnit_Framework_Assert::assertEquals($content, $conditions);
    }

    /**
     * Widget conditions on CMS Page are saved.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget conditions are saved.';
    }
}
