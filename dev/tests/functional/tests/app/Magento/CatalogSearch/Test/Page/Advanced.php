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

namespace Magento\CatalogSearch\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Advanced search page
 *
 */
class Advanced extends Page
{
    /**
     * URL for search advanced page
     */
    const MCA = 'catalogsearch/advanced';

    /**
     * Advanced search form
     *
     * @var string
     */
    protected $searchForm = '.form.search.advanced';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get search block form
     *
     * @return \Magento\CatalogSearch\Test\Block\Form\Advanced
     */
    public function getSearchForm()
    {
        return Factory::getBlockFactory()->getMagentoCatalogSearchFormAdvanced(
            $this->_browser->find($this->searchForm, Locator::SELECTOR_CSS)
        );
    }
}
