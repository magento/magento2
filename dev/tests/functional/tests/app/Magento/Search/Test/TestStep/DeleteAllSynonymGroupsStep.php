<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;

/**
 * Delete all Synonym Groups on backend.
 */
class DeleteAllSynonymGroupsStep implements TestStepInterface
{
    /**
     * Synonym group index page.
     *
     * @var SynonymGroupIndex
     */
    private $synonymGroupIndex;

    /**
     * @param SynonymGroupIndex $synonymGroupIndex
     */
    public function __construct(SynonymGroupIndex $synonymGroupIndex)
    {
        $this->synonymGroupIndex = $synonymGroupIndex;
    }

    /**
     * Delete synonym groups on backend.
     *
     * @return void
     */
    public function run()
    {
        $this->synonymGroupIndex->open();
        $this->synonymGroupIndex->getSynonymGroupGrid()->resetFilter();
        $this->synonymGroupIndex->getSynonymGroupGrid()->massaction([], 'Delete', true, 'Select All');
    }
}
