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
 * @category   Tools
 * @package    view
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\View\Generator;

use Magento\View\Design\ThemeInterface;

/**
 * Lightweight theme that implements minimal required interface
 */
class ThemeLight extends \Magento\Object implements ThemeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->getData('area');
    }

    /**
     * {@inheritdoc}
     */
    public function getThemePath()
    {
        return $this->getData('theme_path');
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPath()
    {
        return $this->getArea() . ThemeInterface::PATH_SEPARATOR . $this->getThemePath();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentTheme()
    {
        return $this->getData('parent_theme');
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return (string)$this->getData('code');
    }

    /**
     * {@inheritdoc}
     */
    public function isPhysical()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getInheritedThemes()
    {
        return array();
    }
}
