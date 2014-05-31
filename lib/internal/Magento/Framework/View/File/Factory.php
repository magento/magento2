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

namespace Magento\Framework\View\File;

use Magento\Framework\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Factory that produces view file instances
 */
class Factory
{
    /**
     * Object manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Return newly created instance of a view file
     *
     * @param string $filename
     * @param string $module
     * @param ThemeInterface|null $theme
     * @param bool $isBase
     * @return \Magento\Framework\View\File
     */
    public function create($filename, $module = '', ThemeInterface $theme = null, $isBase = false)
    {
        return $this->objectManager->create(
            'Magento\Framework\View\File',
            array('filename' => $filename, 'module' => $module, 'theme' => $theme, 'isBase' => $isBase)
        );
    }
}
