<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento;

/**
 * Parses Magento Connect 2.0 package.xml files
 */
class PackageXmlParser extends PathTranslationParser
{
    /**
     * @var string Path to vendor module dir
     */
    protected $_moduleDir = null;

    /**
     * @var \SplFileObject The package.xml file
     */
    protected $_file = null;

    /**
     * @var array Map of package content types to path prefixes
     */
    protected $_targets = array();

    /**
     * Constructor
     *
     * @param string $moduleDir
     * @param string $packageXmlFile
     * @param array  $translations
     */
    public function __construct($moduleDir, $packageXmlFile, $translations = array(), $pathSuffix)
    {
        parent::__construct($translations, $pathSuffix);
        $this->setModuleDir($moduleDir);
        $this->setFile($this->getModuleDir() . '/' . $packageXmlFile);
    }

    /**
     * Sets the module directory where to search for the package.xml file
     *
     * @param string $moduleDir
     * @return PackageXmlParser
     */
    public function setModuleDir($moduleDir)
    {
        // Remove trailing slash
        if (!is_null($moduleDir)) {
            $moduleDir = rtrim($moduleDir, '\\/');
        }

        $this->_moduleDir = $moduleDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getModuleDir()
    {
        return $this->_moduleDir;
    }

    /**
     * @param string|SplFileObject $file
     * @return PackageXmlParser
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new \SplFileObject($file);
        }
        $this->_file = $file;
        return $this;
    }

    /**
     * @return \SplFileObject
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getMappings()
    {
        $file = $this->getFile();

        if (!$file->isReadable()) {
            throw new \ErrorException(sprintf('Package file "%s" not readable', $file->getPathname()));
        }

        $map = $this->_parseMappings();
        $map = $this->translatePathMappings($map);
        return $map;
    }

    /**
     * @throws \ErrorException
     * @return array
     */
    protected function _parseMappings()
    {
        $map = array();

        /** @var $package SimpleXMLElement */
        $package = simplexml_load_file($this->getFile()->getPathname());
        if (isset($package)) {
            foreach ($package->xpath('//contents/target') as $target) {
                try {
                    $basePath = $this->getTargetPath($target);

                    foreach ($target->children() as $child) {
                        foreach ($this->getElementPaths($child) as $elementPath) {
                            $relativePath = $basePath . '/' . $elementPath;
                            $map[] = array($relativePath, $relativePath);
                        }
                    }

                }
                catch (RuntimeException $e) {
                    // Skip invalid targets
                    throw $e;
                    continue;
                }
            }
        }
        return $map;
    }

    /**
     * @param \SimpleXMLElement $target
     * @return string
     * @throws RuntimeException
     */
    protected function getTargetPath(\SimpleXMLElement $target)
    {
        $name = (string) $target->attributes()->name;
        $targets = $this->getTargetsDefinitions();
        if (! isset($targets[$name])) {
            throw new RuntimeException('Invalid target type ' . $name);
        }
        return $targets[$name];
    }

    /**
     * @return array
     */
    protected function getTargetsDefinitions()
    {
        if (! $this->_targets) {

            $targets = simplexml_load_file(__DIR__ . '/../../../../res/target.xml');
            foreach ($targets as $target) {
                $attributes = $target->attributes();
                $this->_targets["{$attributes->name}"] = "{$attributes->uri}";
            }
        }
        return $this->_targets;
    }

    /**
     * @param \SimpleXMLElement $element
     * @return array
     * @throws RuntimeException
     */
    protected function getElementPaths(\SimpleXMLElement $element) {
        $type = $element->getName();
        $name = $element->attributes()->name;
        $elementPaths = array();

        switch ($type) {
            case 'dir':
                if ($element->children()) {
                    foreach ($element->children() as $child) {
                        foreach ($this->getElementPaths($child) as $elementPath) {
                            $elementPaths[] = $name == '.' ? $elementPath : $name . '/' . $elementPath;
                        }
                    }
                } else {
                    $elementPaths[] = $name;
                }
                break;

            case 'file':
                $elementPaths[] = $name;
                break;

            default:
                throw new RuntimeException('Unknown path type: ' . $type);
        }

        return $elementPaths;
    }

    /**
     * @param \SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    protected function getFirstChild(\SimpleXMLElement$element)
    {
        foreach ($element->children() as $child) {
            return $child;
        }
    }
}
