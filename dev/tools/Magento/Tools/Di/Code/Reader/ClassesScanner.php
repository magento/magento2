<?php
/**
 *
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

namespace Magento\Tools\Di\Code\Reader;

use Magento\Framework\Filesystem\FilesystemException;
use Zend\Code\Scanner\FileScanner;

class ClassesScanner
{
    /**
     * @var ClassReaderDecorator
     */
    private $classReaderDecorator;

    /**
     * @param ClassReaderDecorator $classReaderDecorator
     */
    public function __construct(ClassReaderDecorator $classReaderDecorator)
    {
        $this->classReaderDecorator = $classReaderDecorator;
    }

    /**
     * Retrieves list of classes and arguments for given path
     * [CLASS NAME => ConstructorArgument[]]
     *
     * @param string $path
     * @return array
     * @throws FilesystemException
     */
    public function getList($path)
    {
        $realPath = realpath($path);
        if (!(bool)$realPath) {
            throw new FilesystemException();
        }
        $classes = [];
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        /** @var $fileItem \SplFileInfo */
        foreach ($recursiveIterator as $fileItem) {
            if (!$this->isPhpFile($fileItem)) {
                continue;
            }
            $fileScanner = new FileScanner($fileItem->getRealPath());
            $classNames = $fileScanner->getClassNames();
            foreach ($classNames as $className) {
                if (!class_exists($className)) {
                    require_once $fileItem->getRealPath();
                }
                $classes[$className] =  $this->classReaderDecorator->getConstructor($className);
            }
        }
        return $classes;
    }

    /**
     * Whether file is .php file
     *
     * @param \SplFileInfo $item
     * @return bool
     */
    private function isPhpFile(\SplFileInfo $item)
    {
        return $item->isFile() && pathinfo($item->getRealPath(), PATHINFO_EXTENSION) == 'php';
    }
}
