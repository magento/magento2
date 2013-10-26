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
 * Source of layout files that explicitly override base files introduced by modules
 */
namespace Magento\Core\Model\Layout\File\Source\Override;

class Base implements \Magento\Core\Model\Layout\File\SourceInterface
{
    /**
     * @var \Magento\Filesystem
     */
    private $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    private $_dirs;

    /**
     * @var \Magento\Core\Model\Layout\File\Factory
     */
    private $_fileFactory;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Core\Model\Layout\File\Factory $fileFactory
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dirs,
        \Magento\Core\Model\Layout\File\Factory $fileFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles(\Magento\View\Design\ThemeInterface $theme)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        $files = $this->_filesystem->searchKeys(
            $this->_dirs->getDir(\Magento\App\Dir::THEMES),
            "{$themePath}/{$namespace}_{$module}/layout/override/*.xml"
        );
        $result = array();
        foreach ($files as $filename) {
            $moduleDir = dirname(dirname(dirname($filename)));
            $moduleFull = basename($moduleDir);
            $result[] = $this->_fileFactory->create($filename, $moduleFull);
        }
        return $result;
    }
}
