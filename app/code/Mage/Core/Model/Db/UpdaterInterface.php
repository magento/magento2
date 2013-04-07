<?php
/**
 * DB updater interface
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Mage_Core_Model_Db_UpdaterInterface
{
    /**
     * if this node set to true, we will ignore Developer Mode for applying updates
     */
    const XML_PATH_IGNORE_DEV_MODE = 'global/skip_process_modules_updates_ignore_dev_mode';

    /**
     * if this node set to true, we will ignore applying scheme updates
     */
    const XML_PATH_SKIP_PROCESS_MODULES_UPDATES = 'global/skip_process_modules_updates';

    /**
     * Apply database scheme updates whenever needed
     */
    public function updateScheme();

    /**
     * Apply database data updates whenever needed
     */
    public function updateData();
}
