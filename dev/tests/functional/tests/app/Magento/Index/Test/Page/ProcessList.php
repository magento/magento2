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

namespace Magento\Index\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class ProcessList
 * Index Management page
 *
 */
class ProcessList extends Page
{
    /**
     * URL part for index process page
     */
    const MCA = 'admin/process/list/';


    /**
     * Index management grid
     *
     * @var string
     */
    protected $indexProcessGrid = '#indexer_processes_grid';

    /**
     * Global messages block
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get index grid block
     *
     * @return \Magento\Index\Test\Block\Adminhtml\Process\Index
     */
    public function getActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoIndexAdminhtmlProcessIndex(
            $this->_browser->find($this->indexProcessGrid, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesBlock, Locator::SELECTOR_CSS)
        );
    }
}
