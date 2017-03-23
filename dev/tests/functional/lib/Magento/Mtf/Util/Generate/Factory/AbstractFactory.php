<?php
/**
 * @api
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Factory;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Glob;

/**
 * Class AbstractFactory
 *
 * Abstract Factory Generator
 *
 */
abstract class AbstractFactory
{
    protected $type = '';

    protected $cnt = 0;

    protected $factoryContent = '';

    protected $_checkList = [];

    /**
     * Generate Blocks
     *
     * @return void
     */
    public function launch()
    {
        $this->startFactory($this->type);

        $this->generateContent();

        $this->endFactory($this->type);

        \Magento\Mtf\Util\Generate\GenerateResult::addResult($this->type, $this->cnt);
    }

    abstract protected function generateContent();

    /**
     * Add header content
     *
     * @param string $type
     */
    protected function startFactory($type)
    {
        $this->factoryContent = "<?php\n\n";
        $this->factoryContent .= "namespace Magento\Mtf\\{$type}; \n\n";
        $this->factoryContent .= "use Magento\Mtf\\Fixture\\FixtureInterface; \n\n";
        $this->factoryContent .= "class {$type}FactoryDeprecated\n";
        $this->factoryContent .= "{\n";

        $this->factoryContent .= "    /**
     * Object Manager
     *
     * @var \\Magento\Mtf\\ObjectManager
     */
    protected \$objectManager;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        \$this->objectManager = \\Magento\Mtf\\ObjectManager::getInstance();
    }\n";
    }

    /**
     * Add header content
     *
     * @param $type
     * @return $this
     * @throws \RuntimeException
     */
    protected function endFactory($type)
    {
        if (!$this->cnt) {
            return $this;
        }

        $this->checkAndCreateFolder(MTF_BP . "/generated/Magento/Mtf/{$type}");

        $this->factoryContent .= "}\n";

        $file = MTF_BP . "/generated/Magento/Mtf/{$type}/{$type}FactoryDeprecated.php";
        if (false === file_put_contents($file, $this->factoryContent)) {
            throw new \RuntimeException("Can't write content to {$file} file");
        }
    }

    /**
     * Create directory if not exist
     *
     * @param string $folder
     * @param int $mode
     * @return bool
     * @throws \Exception
     */
    protected function checkAndCreateFolder($folder, $mode = 0777)
    {
        if (is_dir($folder)) {
            return true;
        }
        if (!is_dir(dirname($folder))) {
            $this->checkAndCreateFolder(dirname($folder), $mode);
        }
        if (!is_dir($folder) && !$this->mkDir($folder, $mode)) {
            throw new \Exception("Unable to create directory '{$folder}'. Access forbidden.");
        }
        return true;
    }

    /**
     * Create directory
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    protected function mkDir($dir, $mode = 0777, $recursive = true)
    {
        return @mkdir($dir, $mode, $recursive);
    }

    /**
     * Search collect files
     *
     * @param string $type
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function collectItems($type)
    {
        $items = [];
        $rewrites = [];

        $fallbacks = [
            ['path' => 'tests/app'],
            ['path' => 'generated'],
        ];

        while ($fallback = array_pop($fallbacks)) {
            $path = isset($fallback['path']) ? $fallback['path'] : '';
            $ns = isset($fallback['namespace']) ? $fallback['namespace'] : '';
            $location = $path . ($ns ? ('/' . str_replace('\\', '/', $ns)) : '');

            $pattern = $this->_getPattern($type, $location);

            $filesIterator = Glob::glob($pattern, Glob::GLOB_BRACE);

            foreach ($filesIterator as $filePath) {
                if (!is_dir($filePath)) {
                    $this->_processItem($items, $rewrites, $filePath, $location, $path);
                } else {
                    $dirIterator = new \RegexIterator(
                        new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator(
                                $filePath,
                                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
                            )
                        ),
                        '/.php$/i'
                    );
                    foreach ($dirIterator as $info) {
                        /** @var $info \SplFileInfo */
                        $realPath = $info->getPathname();
                        if (is_link($realPath)) {
                            $realPath = readlink($realPath);
                        }
                        $this->_processItem($items, $rewrites, $realPath, $location, $path);
                    }
                }
            }
        }
        return $items;
    }

    /**
     * Handling file
     *
     * @param array $items
     * @param array $rewrites
     * @param string $filename
     * @param string $location
     * @param string $path
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _processItem(& $items, & $rewrites, $filename, $location, $path)
    {
        $filename = str_replace('\\', '/', $filename);

        $posTestsPath = strpos($filename, $path);
        $posClassName = $posTestsPath + strlen($path);
        $classPath = str_replace('.php', '', $filename);
        $className = str_replace('/', '\\', substr($classPath, $posClassName));

        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->isAbstract()) {
            return;
        }
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations($className);

        list(, $targetClassName) = explode($location . '/', $filename);
        $targetClassName = str_replace('.php', '', $targetClassName);
        $targetClassName = str_replace('/', '\\', $targetClassName);

        if (isset($this->_checkList[$targetClassName])) {
            $annotations['class']['rewrite'][0] = $this->_checkList[$targetClassName];
            $this->_checkList[$targetClassName] = $className;
        } else {
            $this->_checkList[$targetClassName] = $className;
        }

        if (isset($annotations['class']['rewrite'])) {
            $original = $annotations['class']['rewrite'][0];

            if (isset($items[$original])) {
                if (isset($items[$original]['fallback'])) {
                    $message = "Class '{$className}' rewrites class '{$original}'.\n";
                    $prevClass = key($items[$original]['fallback']);
                    $message .= "Class '{$prevClass}' also rewrites class '$original'";
                    throw new \Exception("Multiple rewrites detected:\n" . $message);
                }

                if (isset($items[$className])) {
                    $items[$original]['fallback'][$className] = $items[$className];
                } else {
                    $items[$original]['fallback'][$className]['class'] = $className;
                }

                $rewrites[$className] = &$items[$original]['fallback'][$className];

                if (isset($items[$className])) {
                    unset($items[$className]);
                }
            } elseif (isset($rewrites[$original])) {
                if (isset($rewrites[$original]['fallback'])) {
                    $message = "Class '{$className}' rewrites class '{$original}'.\n";
                    $prevClass = key($rewrites[$original]['fallback']);
                    $message .= "Class '{$prevClass}' also rewrites class '$original'";
                    throw new \Exception("Multiple rewrites detected:\n" . $message);
                }

                if (isset($items[$className])) {
                    $rewrites[$original]['fallback'][$className] = $items[$className];
                } else {
                    $rewrites[$original]['fallback'][$className]['class'] = $className;
                }

                $rewrites[$className] = &$rewrites[$original]['fallback'][$className];

                if (isset($items[$className])) {
                    unset($items[$className]);
                }
            } else {
                $items[$original]['class'] = $original;
                if (isset($items[$className])) {
                    $items[$original]['fallback'][$className] = $items[$className];
                } else {
                    $items[$original]['fallback'][$className]['class'] = $className;
                }

                $rewrites[$className] = &$items[$original]['fallback'][$className];

                if (isset($items[$className])) {
                    unset($items[$className]);
                }
            }
        } else {
            $items[$className]['class'] = $className;
        }
    }

    /**
     * Convert class name to camel-case
     *
     * @param string $class
     * @return string
     */
    protected function _toCamelCase($class)
    {
        $class = str_replace('_', ' ', $class);
        $class = str_replace('\\', ' ', $class);
        $class = str_replace('/', ' ', $class);

        return str_replace(' ', '', ucwords($class));
    }

    /**
     * Find class depends on fallback configuration
     *
     * @param array $item
     * @return string
     */
    protected function _resolveClass(array $item)
    {
        if (isset($item['fallback'])) {
            return $this->_resolveClass(reset($item['fallback']));
        }
        return $item['class'];
    }

    /**
     * Return comment text for item
     *
     * @param array $item
     * @param string $arguments
     * @return string
     */
    protected function _buildFallbackComment(array $item, $arguments = '')
    {
        if (isset($item['fallback'])) {
            $returnComment = "\n        //return new \\" . $item['class'] . "({$arguments});";
            return $this->_buildFallbackComment(reset($item['fallback']), $arguments) . $returnComment;
        }
    }

    /**
     * Return pattern depends on configuration
     *
     * @param string $type
     * @param string $location
     * @throws \RuntimeException
     * @return string
     */
    protected function _getPattern($type, $location)
    {
        $globPath = MTF_BP . '/' . $location . '/*/*/Test/' . $type;
        return $globPath;
    }
}
