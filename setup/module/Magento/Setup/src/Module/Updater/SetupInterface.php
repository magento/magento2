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
namespace Magento\Setup\Module\Updater;

interface SetupInterface
{
    const DEFAULT_SETUP_CONNECTION = 'setup_setup';

    const VERSION_COMPARE_EQUAL = 0;

    const VERSION_COMPARE_LOWER = -1;

    const VERSION_COMPARE_GREATER = 1;

    const TYPE_DB_INSTALL = 'install';

    const TYPE_DB_UPGRADE = 'upgrade';

    const TYPE_DB_RECURRING = 'recurring';

    const TYPE_DATA_INSTALL = 'data-install';

    const TYPE_DATA_UPGRADE = 'data-upgrade';

    /**
     * Check call afterApplyAllUpdates method for setup class
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCallAfterApplyAllUpdates();

    /**
     * Run each time after applying of all updates,
     *
     * @return \Magento\Setup\Module\Updater\SetupInterface
     */
    public function afterApplyAllUpdates();

    /**
     *  Apply data updates to the system after upgrading
     *
     * @return void
     */
    public function applyDataUpdates();
}
