<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Cms\Test\Page\Adminhtml\CmsBlockIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsBlockNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Preconditions:
 * 1. Create CMS Block.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Content > Blocks.
 * 3. Open created CMS block.
 * 4. Click "Delete Block".
 * 5. Perform all assertions.
 *
 * @group CMS_Content
 * @ZephyrId MAGETWO-25698
 */
class DeleteCmsBlockEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Page CmsBlockIndex.
     *
     * @var CmsBlockIndex
     */
    protected $cmsBlockIndex;

    /**
     * Page CmsBlockNew.
     *
     * @var CmsBlockNew
     */
    protected $cmsBlockNew;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data.
     *
     * @param CmsBlockIndex $cmsBlockIndex
     * @param CmsBlockNew $cmsBlockNew
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CmsBlockIndex $cmsBlockIndex,
        CmsBlockNew $cmsBlockNew,
        FixtureFactory $fixtureFactory
    ) {
        $this->cmsBlockIndex = $cmsBlockIndex;
        $this->cmsBlockNew = $cmsBlockNew;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Delete CMS Block.
     *
     * @param CmsBlock $cmsBlock
     * @return array
     */
    public function test(CmsBlock $cmsBlock)
    {
        // Precondition
        $cmsBlock->persist();
        $filter = ['identifier' => $cmsBlock->getIdentifier()];
        $category = $this->createCategory($cmsBlock);

        // Steps
        $this->cmsBlockIndex->open();
        $this->cmsBlockIndex->getCmsBlockGrid()->searchAndOpen($filter);
        $this->cmsBlockNew->getFormPageActions()->delete();
        $this->cmsBlockNew->getModalBlock()->acceptAlert();

        return ['category' => $category];
    }

    /**
     * Create category.
     *
     * @param CmsBlock $cmsBlock
     * @return Category
     */
    private function createCategory(CmsBlock $cmsBlock)
    {
        $category = $this->fixtureFactory->createByCode(
            'category',
            [
                'dataset' => 'default_subcategory',
                'data' => [
                    'display_mode' => 'Static block and products',
                    'landing_page' => $cmsBlock->getTitle(),
                ]
            ]
        );
        $category->persist();

        return $category;
    }
}
