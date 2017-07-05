<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. A customer is created.
 * 2. A cms page is created.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to Content > Elements > Pages.
 * 3. Search and select the cms page for edit.
 * 4. Add a form with webapi request and 'Submit Request' button in content field.
 * 5. Save the page.
 * 6. Login customer from store front.
 * 7. Navigate to cms page.
 * 8. Click the 'Submit Request' button.
 * 9. Perform all assertions.
 *
 * @group Webapi
 * @ZephyrId MAGETWO-64389
 * @security-private
 */
class CustomerWebapisPermissionTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * CMS Index page.
     *
     * @var CmsPageIndex
     */
    private $cmsPageIndex;

    /**
     * Edit CMS page.
     *
     * @var CmsPageNew
     */
    private $cmsPageNew;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    private $factory;

    /**
     * Inject pages.
     *
     * @param CmsPageIndex $cmsPageIndex
     * @param CmsPageNew $cmsPageNew
     * @param CmsPage $cmsOriginal
     * @param FixtureFactory $factory
     * @return array
     */
    public function __inject(
        CmsPageIndex $cmsPageIndex,
        CmsPageNew $cmsPageNew,
        CmsPage $cmsOriginal,
        FixtureFactory $factory
    ) {
        $cmsOriginal->persist();
        $this->cmsPageIndex = $cmsPageIndex;
        $this->cmsPageNew = $cmsPageNew;
        $this->factory = $factory;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'wysiwyg_disabled']
        )->run();

        return ['cmsOriginal' => $cmsOriginal];
    }

    /**
     * Construct cms page with a form that contains webapi request and 'Submit Request' button.
     *
     * @param CmsPage $cmsOriginal
     * @param string $url
     * @param string $method
     * @return array
     */
    public function test(CmsPage $cmsOriginal, $url, $method = 'POST')
    {
        $this->cmsPageIndex->open();
        $this->cmsPageIndex->getCmsPageGridBlock()->searchAndOpen(['title' => $cmsOriginal->getTitle()]);
        $data = $cmsOriginal->getData();
        $content = <<<HTML
            <form action="$url" method="$method" >
                <input type="submit" value="Submit Request" />
            </form>
HTML;
        $data['content'] = ['content' => $content];
        $cms = $this->factory->createByCode('cmsPage', ['data' => $data]);
        $this->cmsPageNew->getPageForm()->fill($cms);
        $this->cmsPageNew->getPageMainActions()->save();
        
        return ['cms' => $cms];
    }
}
