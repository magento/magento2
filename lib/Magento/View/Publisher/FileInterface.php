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
namespace Magento\View\Publisher;

/**
 * Publisher file interface
 */
interface FileInterface
{
    /**#@+
     * Public directories prefix group
     */
    const PUBLIC_MODULE_DIR = '_module';

    const PUBLIC_VIEW_DIR = '_view';

    const PUBLIC_THEME_DIR = '_theme';

    /**#@-*/

    /**
     * Check is publication allowed for a file
     *
     * @return bool
     */
    public function isPublicationAllowed();

    /**
     * Build unique file path for publication
     *
     * @return string
     */
    public function buildUniquePath();

    /**
     * Original file extension
     *
     * @return string
     */
    public function getExtension();

    /**
     * @return bool
     */
    public function isSourceFileExists();

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @return array
     */
    public function getViewParams();

    /**
     * Build path to file located in public folder
     *
     * @return string
     */
    public function buildPublicViewFilename();

    /**
     * Returns absolute path
     *
     * @return string|null
     */
    public function getSourcePath();
}
