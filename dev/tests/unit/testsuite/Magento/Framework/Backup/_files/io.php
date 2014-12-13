<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
