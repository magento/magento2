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

namespace Magento\Framework\View;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * View file in the file system with context of its identity
 */
class File
{
    /**
     * File name
     *
     * @var string
     */
    protected $filename;

    /**
     * Module
     *
     * @var string
     */
    protected $module;

    /**
     * Theme
     *
     * @var ThemeInterface
     */
    protected $theme;

    /**
     * Base flag
     *
     * @var string
     */
    protected $isBase;

    /**
     * Identifier
     *
     * @var string
     */
    protected $identifier;

    /**
     * Constructor
     *
     * @param string $filename
     * @param string $module
     * @param ThemeInterface $theme
     * @param bool $isBase
     */
    public function __construct($filename, $module, ThemeInterface $theme = null, $isBase = false)
    {
        $this->filename = $filename;
        $this->module = $module;
        $this->theme = $theme;
        $this->isBase = $isBase;
    }

    /**
     * Retrieve full filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Retrieve name of a file without a directory path
     *
     * @return string
     */
    public function getName()
    {
        return basename($this->filename);
    }

    /**
     * Retrieve fully-qualified name of a module a file belongs to
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Retrieve instance of a theme a file belongs to
     *
     * @return ThemeInterface|null
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Whether file is a base one
     *
     * @return bool
     */
    public function isBase()
    {
        return is_null($this->theme);
    }

    /**
     * Calculate unique identifier for a view file
     *
     * @return string
     */
    public function getFileIdentifier()
    {
        if (null === $this->identifier) {
            $theme = $this->getTheme() ? ('|theme:' . $this->theme->getFullPath()) : '';
            $this->identifier = ($this->isBase ? 'base' : '')
                . $theme . '|module:' . $this->getModule() . '|file:' . $this->getName();
        }
        return $this->identifier;
    }
}
