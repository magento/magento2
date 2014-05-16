<?php
/**
 * Store creation page
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
namespace Magento\Backend\Test\Page\System\Store;

use Mtf\Factory\Factory;
use Mtf\Page\Page;

class NewStore extends Page
{
    const MCA = 'admin/system_store/newStore';

    /**
     * Store edit form block
     *
     * @var string
     */
    protected $formBlock = '#edit_form';

    /**
     * Page actions block
     *
     * @var string
     */
    protected $actionsBlock = '.page-actions';

    /**
     * Initialize page
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Retrieve form block
     *
     * @return \Magento\Backend\Test\Block\System\Store\Edit
     */
    public function getFormBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendSystemStoreEdit(
            $this->_browser->find($this->formBlock)
        );
    }

    /**
     * Retrieve actions block
     *
     * @return \Magento\Backend\Test\Block\System\Store\Actions
     */
    public function getPageActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendSystemStoreActions(
            $this->_browser->find($this->actionsBlock)
        );
    }
}
