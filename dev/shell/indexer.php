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
 * @category    Magento
 * @package     Magento_Shell
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../app/bootstrap.php';
$params = array(
    \Magento\Core\Model\App::PARAM_RUN_CODE => 'admin',
    \Magento\Core\Model\App::PARAM_RUN_TYPE => 'store',
);

$entryPoint = new \Magento\Index\Model\EntryPoint\Shell(
    basename(__FILE__),
    new \Magento\Index\Model\EntryPoint\Shell\ErrorHandler(),
    new \Magento\Core\Model\Config\Primary(BP, $params)
);
$entryPoint->processRequest();
