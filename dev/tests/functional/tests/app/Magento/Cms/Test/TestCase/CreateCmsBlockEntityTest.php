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
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Content > Blocks.
 * 3. Click "Add New Block" button.
 * 4. Fill data according to dataset.
 * 5. Perform all assertions.
 *
 * @group CMS_Content_(PS)
 * @ZephyrId MAGETWO-25578
 */
class CreateCmsBlockEntityTest extends AbstractCmsBlockEntityTest
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Create CMS Block.
     *
     * @param CmsBlock $cmsBlock
     * @return void
     */
    public function test(CmsBlock $cmsBlock)
    {
        // Prepare data for tearDown
        $this->storeName = $cmsBlock->getStores();

        // Steps
        $this->cmsBlockIndex->open();
        $this->cmsBlockIndex->getGridPageActions()->addNew();
        $this->cmsBlockNew->getCmsForm()->fill($cmsBlock);
        $this->cmsBlockNew->getFormPageActions()->save();
    }
}
