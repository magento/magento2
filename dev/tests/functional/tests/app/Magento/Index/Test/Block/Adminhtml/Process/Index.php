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

namespace Magento\Index\Test\Block\Adminhtml\Process;

use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Index
 * Index management grid
 *
 */
class Index extends Grid
{
    /**
     * @var string
     */
    protected $actionsDropdown = '#massaction-select';

    /**
     * @var string
     */
    protected $selectAll = './/option[@value="selectAll"]';

    /**
     * Mass action for Reindex Data
     */
    public function reindexAll()
    {
        $this->_rootElement->find($this->actionsDropdown, Locator::SELECTOR_CSS, 'select')->setValue('Select All');
        $this->_rootElement->find($this->massactionSubmit, Locator::SELECTOR_CSS)->click();
    }
}
