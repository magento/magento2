<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsBlock;

/**
 * Preconditions:
 * 1. Create store view.
 * 2. Create CMS Block.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Content > Blocks.
 * 3. Open created CMS block.
 * 4. Fill data according to dataset.
 * 5. Perform all assertions.
 *
 * @group CMS_Content_(PS)
 * @ZephyrId MAGETWO-25941
 */
class UpdateCmsBlockEntityTest extends AbstractCmsBlockEntityTest
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Run Update CMS Block test.
     *
     * @param CmsBlock $initialCmsBlock
     * @param CmsBlock $cmsBlock
     * @return void
     */
    public function test(CmsBlock $initialCmsBlock, CmsBlock $cmsBlock)
    {
        // Prepare data for tearDown
        $this->storeName = $cmsBlock->getStores();

        // Precondition
        $initialCmsBlock->persist();

        // Steps
        $this->cmsBlockIndex->open();
        $this->cmsBlockIndex->getCmsBlockGrid()->searchAndOpen(['identifier' => $initialCmsBlock->getIdentifier()]);
        $this->cmsBlockNew->getCmsForm()->fill($cmsBlock);
        $this->cmsBlockNew->getFormPageActions()->save();
    }
}
