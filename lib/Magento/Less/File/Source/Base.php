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
namespace Magento\Less\File\Source;

use Magento\View\Layout\File\SourceInterface;
use Magento\View\Design\ThemeInterface;
use Magento\App\Filesystem;
use Magento\Filesystem\Directory\ReadInterface;
use Magento\View\Layout\File\Factory;

/**
 * Source of base layout files introduced by modules
 */
class Base implements SourceInterface
{
    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var ReadInterface
     */
    protected $modulesDirectory;

    /**
     * @param Filesystem $filesystem
     * @param Factory $fileFactory
     */
    public function __construct(Filesystem $filesystem, Factory $fileFactory)
    {
        $this->modulesDirectory = $filesystem->getDirectoryRead(Filesystem::MODULES_DIR);
        $this->fileFactory = $fileFactory;
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array|\Magento\View\Layout\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath = '*')
    {
        //Import module base styles

        $filePath = pathinfo($filePath, PATHINFO_EXTENSION) ? $filePath : rtrim($filePath, '.') . '.less';

        $namespace = $module = '*';
        $area = $theme->getArea();
        $files = $this->modulesDirectory->search("{$namespace}/{$module}/view/{$area}/{$filePath}");
        $result = array();
        $filePath = strtr(preg_quote($filePath), array('\*' => '[^/]+'));
        $pattern = "#(?<namespace>[^/]+)/(?<module>[^/]+)/view/{$area}/" . $filePath . "$#i";
        foreach ($files as $file) {
            $filename = $this->modulesDirectory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $moduleFull = "{$matches['namespace']}_{$matches['module']}";
            $result[] = $this->fileFactory->create($filename, $moduleFull, $theme);
        }
        return $result;
    }
}
