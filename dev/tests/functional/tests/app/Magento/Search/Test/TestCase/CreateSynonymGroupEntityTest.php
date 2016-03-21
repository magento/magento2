<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestCase;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Search\Test\Page\Adminhtml\SynonymGroupNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Search\Test\Fixture\SynonymGroup;

/**
 * Preconditions:
 * 1. Create store view.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Marketing > Search Synonyms.
 * 3. Click "New Synonym Group" button.
 * 4. Fill data according to dataset.
 * 5. Perform all assertions.
 *
 * @group Search_(MX)
 * @ZephyrId MAGETWO-47681
 */
class CreateSynonymGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Page Index.
     *
     * @var synonymGroupIndex
     */
    protected $synonymGroupIndex;

    /**
     * Page synonymGroupNew.
     *
     * @var SynonymGroupNew
     */
    protected $synonymGroupNew;

    /**
     * Injection data.
     *
     * @param SynonymGroupIndex $synonymGroupIndex
     * @param SynonymGroupNew $synonymGroupNew
     * @return void
     */
    public function __inject(
        SynonymGroupIndex $synonymGroupIndex,
        SynonymGroupNew $synonymGroupNew
    ) {
        $this->synonymGroupIndex = $synonymGroupIndex;
        $this->synonymGroupNew = $synonymGroupNew;
    }

    /**
     * Create Synonym Group.
     *
     * @param SynonymGroup $synonymGroup
     * @return void
     */
    public function test(SynonymGroup $synonymGroup)
    {
        // Steps
        $this->synonymGroupIndex->open();
        $this->synonymGroupIndex->getGridPageActions()->addNew();
        $this->synonymGroupNew->getSynonymGroupForm()->fill($synonymGroup);
        $this->synonymGroupNew->getFormPageActions()->save();
    }
}
