<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Pack\Writer\File;

/**
 * Mock is_dir function
 *
 * @see \Magento\Setup\Module\I18n\Pack\Writer\File\AbstractFile
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_dir($path)
{
    return false;
}

/**
 * Mock mkdir function
 *
 * @see \Magento\Setup\Module\I18n\Pack\Writer\File\AbstractFile
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function mkdir($pathname, $mode = 0777, $recursive = false, $context = null)
{
    return true;
}

/**
 * Mock chmod function
 *
 * @see \Magento\Setup\Module\I18n\Pack\Writer\File\AbstractFile
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function chmod($filename, $mode)
{
    return true;
}
