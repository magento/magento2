<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\System\Store;

use Mtf\Client\Element\Locator;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class StoreGrid
 * Adminhtml Store View management grid
 */
class StoreGrid extends GridInterface
{
    /**
     * Locator value for opening needed row
     *
     * @var string
     */
    protected $editLink = 'td[data-column="store_title"] > a';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'store_title' => [
            'selector' => '#storeGrid_filter_store_title',
        ],
        'group_title' => [
            'selector' => '#storeGrid_filter_group_title'
        ]
    ];

    /**
     * Store title format for XPATH
     *
     * @var string
     */
    protected $titleFormat = '//td[a[.="%s"]]';

    /**
     * Store name link selector
     *
     * @var string
     */
    protected $storeName = '//a[.="%s"]';

    /**
     * Check if store exists
     *
     * @param string $title
     * @return bool
     */
    public function isStoreExists($title)
    {
        $element = $this->_rootElement->find(sprintf($this->titleFormat, $title), Locator::SELECTOR_XPATH);
        return $element->isVisible();
    }

    /**
     * Click to appropriate store in Store grid for edit
     *
     * @param string $name
     * @return void
     */
    public function editStore($name)
    {
        $this->_rootElement->find(sprintf($this->storeName, $name), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Search and open appropriate store
     *
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function searchAndOpenStore(StoreGroup $storeGroup)
    {
        $storeName = $storeGroup->getName();
        $this->search(['group_title' => $storeName]);
        $this->editStore($storeName);
    }
}
