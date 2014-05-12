<?php
/**
 * Store configuration form page
 *
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

namespace Magento\Backend\Test\Page\System;

use Magento\Backend\Test\Block\System\Config\Switcher;
use Magento\Core\Test\Block\Messages;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory,
    Mtf\Page\Page;

class Config extends Page
{
    /**
     * Url
     */
    const MCA = 'admin/system_config';

    /**
     * Config Edit form selector
     *
     * @var string
     */
    protected $form = '#config-edit-form';

    /**
     * Page actions selector
     *
     * @var string
     */
    protected $pageActions = '.page-main-actions';

    /**
     * Messages selector
     *
     * @var string
     */
    protected $messages = '#messages';

    /**
     * Constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Retrieve form block
     *
     * @return \Magento\Backend\Test\Block\System\Config\Form
     */
    public function getForm()
    {
        return Factory::getBlockFactory()->getMagentoBackendSystemConfigForm($this->_browser->find($this->form));
    }

    /**
     * Retrieve page actions block
     *
     * @return \Magento\Backend\Test\Block\System\Config\PageActions
     */
    public function getPageActions()
    {
        return Factory::getBlockFactory()->getMagentoBackendSystemConfigPageActions(
            $this->_browser->find($this->pageActions)
        );
    }

    /**
     * Retrieve messages block
     *
     * @return Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find($this->messages));
    }
}
