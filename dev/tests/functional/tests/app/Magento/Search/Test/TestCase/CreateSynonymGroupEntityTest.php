<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\StoreDelete;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreNew;
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
 * @group Search_(PS)
 * @ZephyrId MAGETWO-47681
 */
class CreateSynonymGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
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
     * Page StoreIndex.
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page StoreNew.
     *
     * @var StoreNew
     */
    protected $storeNew;

    /**
     * Page StoreDelete.
     *
     * @var StoreDelete
     */
    protected $storeDelete;

    /**
     * Store Name.
     *
     * @var array
     */
    protected $storeName;

    /**
     * Skipped stores for tearDown.
     *
     * @var array
     */
    protected $skippedStores = [
        'All Store Views',
        'Default Store View',
    ];

    /**
     * Injection data.
     *
     * @param SynonymGroupIndex $synonymGroupIndex
     * @param SynonymGroupNew $synonymGroupNew
     * @param StoreIndex $storeIndex
     * @param StoreNew $storeNew
     * @param StoreDelete $storeDelete
     * @return void
     */
    public function __inject(
        SynonymGroupIndex $synonymGroupIndex,
        SynonymGroupNew $synonymGroupNew,
        StoreIndex $storeIndex,
        StoreNew $storeNew,
        StoreDelete $storeDelete
    ) {
        $this->synonymGroupIndex = $synonymGroupIndex;
        $this->synonymGroupNew = $synonymGroupNew;
        $this->storeIndex = $storeIndex;
        $this->storeNew = $storeNew;
        $this->storeDelete = $storeDelete;
    }

    /**
     * Delete Store after test.
     *
     * @return void
     */
    public function tearDown()
    {
//        foreach ($this->storeName as $store) {
//            if (in_array($store, $this->skippedStores) or $store === null) {
//                continue;
//            }
//            $tmp = explode("/", $store);
//            $filter['store_title'] = end($tmp);
//            $this->storeIndex->open();
//            $this->storeIndex->getStoreGrid()->searchAndOpen($filter);
//            $this->storeNew->getFormPageActions()->delete();
//            $this->storeDelete->getStoreForm()->fillForm(['create_backup' => 'No']);
//            $this->storeDelete->getFormPageActions()->delete();
//        }
    }

    /**
     * Create Synonym Group.
     *
     * @param SynonymGroup $synonymGroup
     * @return void
     */
    public function test(SynonymGroup $synonymGroup)
    {
        // Prepare data for tearDown
        $this->storeName[] = $synonymGroup->getScopeId();

        // Steps
        $this->synonymGroupIndex->open();
        $this->synonymGroupIndex->getGridPageActions()->addNew();
        $this->synonymGroupNew->getSynonymGroupForm()->fill($synonymGroup);
        $this->synonymGroupNew->getFormPageActions()->save();
    }
}
