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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout file in the file system with context of its identity
 */
namespace Magento\Core\Model\Layout;

class File
{
    /**
     * @var string
     */
    private $_filename;

    /**
     * @var string
     */
    private $_module;

    /**
     * @var \Magento\View\Design\ThemeInterface
     */
    private $_theme;

    /**
     * @param string $filename
     * @param string $module
     * @param \Magento\View\Design\ThemeInterface $theme
     */
    public function __construct($filename, $module, \Magento\View\Design\ThemeInterface $theme = null)
    {
        $this->_filename = $filename;
        $this->_module = $module;
        $this->_theme = $theme;
    }

    /**
     * Retrieve full filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Retrieve name of a file without a directory path
     *
     * @return string
     */
    public function getName()
    {
        return basename($this->_filename);
    }

    /**
     * Retrieve fully-qualified name of a module a file belongs to
     *
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Retrieve instance of a theme a file belongs to
     *
     * @return \Magento\View\Design\ThemeInterface|null
     */
    public function getTheme()
    {
        return $this->_theme;
    }

    /**
     * Whether file is a base one
     *
     * @return bool
     */
    public function isBase()
    {
        return is_null($this->_theme);
    }
}
