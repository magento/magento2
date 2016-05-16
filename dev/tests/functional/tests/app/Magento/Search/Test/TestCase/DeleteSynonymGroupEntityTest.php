<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestCase;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Search\Test\Page\Adminhtml\SynonymGroupNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Search\Test\Fixture\SynonymGroup;

/**
 * Preconditions:
 * 1. Create Synonym Group view.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Marketing > Search Synonyms.
 * 3. Delete created Synonym Group
 * 4. Perform all assertions.
 *
 * @group Search_(MX)
 * @ZephyrId MAGETWO-47683
 */
class DeleteSynonymGroupEntityTest extends Injectable
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
     * Update Synonym Group.
     *
     * @param SynonymGroup $initialSynonymGroup
     * @return void
     */
    public function test(SynonymGroup $initialSynonymGroup)
    {
        //precondition
        $initialSynonymGroup->persist();

        $initialData = ($initialSynonymGroup->getData());
        $synonyms = $initialData['synonyms'];

        // Steps
        $this->synonymGroupIndex->open();
        $this->synonymGroupIndex->getSynonymGroupGrid()->searchAndOpen(['synonyms' => $synonyms]);
        $this->synonymGroupNew->getFormPageActions()->delete();
        $this->synonymGroupNew->getModalBlock()->acceptAlert();
    }
}
