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

namespace Magento\Backend\Test\Page\Urlrewrite;

use Mtf\Page\Page,
    Mtf\Factory\Factory;

/**
 * Class UrlrewriteGrid
 * Backend URL rewrite grid page
 *
 */
class UrlrewriteGrid extends Page
{
    /**
     * URL for URL rewrite grid
     */
    const MCA = 'admin/urlrewrite/index';

    /**
     * Page actions block UI ID
     *
     * @var string
     */
    protected $pageActionsBlock = '.page-actions';

    /**
     * Messages block UI ID
     *
     * @var string
     */

    protected $messagesBlock = '.messages .messages';

    /**
     * Init page. Set page URL.
     */
    protected function _init()
    {
        parent::_init();
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Retrieve page actions block
     *
     * @return \Magento\Backend\Test\Block\Urlrewrite\Actions
     */
    public function getPageActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendUrlrewriteActions(
            $this->_browser->find($this->pageActionsBlock)
        );
    }

    /**
     * Retrieve messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesBlock)
        );
    }
}
