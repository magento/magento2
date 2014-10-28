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
namespace Magento\Framework\Backup;


/**
 * Mock is_dir function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_dir($path)
{
    return true;
}

/**
 * Mock is_dir function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_writable($path)
{
    return true;
}

/**
 * Mock disk_free_space function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function disk_free_space($path)
{
    return 2;
}

/**
 * Mock is_file function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_file($path)
{
    return 2;
}

/**
 * Mock filesize function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function filesize($path)
{
    return 1;
}

/**
 * Mock unlink function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function unlink($path)
{
    return true;
}

/**
 * Mock rmdir function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function rmdir($path)
{
    return true;
}
