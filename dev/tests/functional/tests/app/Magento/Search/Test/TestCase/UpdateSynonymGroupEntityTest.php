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
 * 3. Open created Synonym Group.
 * 4. Fill data according to dataset.
 * 5. Perform all assertions.
 *
 * @group Search_(MX)
 * @ZephyrId MAGETWO-49412
 */
class UpdateSynonymGroupEntityTest extends Injectable
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
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $factory;

    /**
     * Injection data.
     *
     * @param SynonymGroupIndex $synonymGroupIndex
     * @param SynonymGroupNew $synonymGroupNew
     * @param FixtureFactory $factory
     * @return void
     */
    public function __inject(
        SynonymGroupIndex $synonymGroupIndex,
        SynonymGroupNew $synonymGroupNew,
        FixtureFactory $factory
    ) {
        $this->synonymGroupIndex = $synonymGroupIndex;
        $this->synonymGroupNew = $synonymGroupNew;
        $this->factory = $factory;
    }

    /**
     * Update Synonym Group.
     *
     * @param SynonymGroup $initialSynonymGroup
     * @param SynonymGroup $synonymGroup
     * @return void
     */
    public function test(SynonymGroup $initialSynonymGroup, SynonymGroup $synonymGroup)
    {
        //precondition
        $initialSynonymGroup->persist();

        $initialData = ($initialSynonymGroup->getData());
        $synonyms = $initialData['synonyms'];

        // Steps
        $this->synonymGroupIndex->open();
        $this->synonymGroupIndex->getSynonymGroupGrid()->searchAndOpen(['synonyms' => $synonyms]);
        $this->synonymGroupNew->getSynonymGroupForm()->fill($synonymGroup);
        $this->synonymGroupNew->getFormPageActions()->save();
    }
}
