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
 * @group CMS_Content_(PS)
 * @ZephyrId MAGETWO-25698
 */
class DeleteCmsBlockEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
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
     * Injection data.
     *
     * @param CmsBlockIndex $cmsBlockIndex
     * @param CmsBlockNew $cmsBlockNew
     * @return void
     */
    public function __inject(
        CmsBlockIndex $cmsBlockIndex,
        CmsBlockNew $cmsBlockNew
    ) {
        $this->cmsBlockIndex = $cmsBlockIndex;
        $this->cmsBlockNew = $cmsBlockNew;
    }

    /**
     * Delete CMS Block.
     *
     * @param CmsBlock $cmsBlock
     * @return void
     */
    public function test(CmsBlock $cmsBlock)
    {
        // Precondition
        $cmsBlock->persist();
        $filter = ['identifier' => $cmsBlock->getIdentifier()];

        // Steps
        $this->cmsBlockIndex->open();
        $this->cmsBlockIndex->getCmsBlockGrid()->searchAndOpen($filter);
        $this->cmsBlockNew->getFormPageActions()->delete();
        $this->cmsBlockNew->getModalBlock()->acceptAlert();
    }
}
