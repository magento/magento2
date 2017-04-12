<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\StoreDelete;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreNew;
use Magento\Cms\Test\Page\Adminhtml\CmsBlockIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsBlockNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Parent class for CMS Block tests.
 */
abstract class AbstractCmsBlockEntityTest extends Injectable
{
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
        'Main Website/Main Website Store/Default Store View',
    ];

    /**
     * Injection data.
     *
     * @param CmsBlockIndex $cmsBlockIndex
     * @param CmsBlockNew $cmsBlockNew
     * @param StoreIndex $storeIndex
     * @param StoreNew $storeNew
     * @param StoreDelete $storeDelete
     * @return void
     */
    public function __inject(
        CmsBlockIndex $cmsBlockIndex,
        CmsBlockNew $cmsBlockNew,
        StoreIndex $storeIndex,
        StoreNew $storeNew,
        StoreDelete $storeDelete
    ) {
        $this->cmsBlockIndex = $cmsBlockIndex;
        $this->cmsBlockNew = $cmsBlockNew;
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
        foreach ($this->storeName as $store) {
            if (in_array($store, $this->skippedStores)) {
                continue;
            }
            $tmp = explode("/", $store);
            $filter['store_title'] = end($tmp);
            $this->storeIndex->open();
            $this->storeIndex->getStoreGrid()->searchAndOpen($filter);
            $this->storeNew->getFormPageActions()->delete();
            $this->storeDelete->getStoreForm()->fillForm(['create_backup' => 'No']);
            $this->storeDelete->getFormPageActions()->delete();
        }
    }
}
