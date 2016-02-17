<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento;

/**
 * Parsers modman files
 */
class ModmanParser extends PathTranslationParser
{
    /**
     * @var string Path to vendor module dir
     */
    protected $_moduleDir = null;

    /**
     * @var \SplFileObject The modman file
     */
    protected $_file = null;

    /**
     * Constructor
     *
     * @param string $moduleDir
     */
    public function __construct($moduleDir = null, $translations = array(), $pathSuffix)
    {
        parent::__construct($translations, $pathSuffix);

        $this->setModuleDir($moduleDir);
        $this->setFile($this->getModmanFile());
    }

    /**
     * Sets the module directory where to search for the modman file
     *
     * @param string $moduleDir
     * @return ModmanParser
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
     * @return ModmanParser
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
     * @return string
     */
    public function getModmanFile()
    {
        $file = null;
        if (!is_null($this->_moduleDir)) {
            $file = new \SplFileObject($this->_moduleDir . '/modman');
        }
        return $file;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getMappings()
    {
        $file = $this->getFile();

        if (!$file->isReadable()) {
            throw new \ErrorException(sprintf('modman file "%s" not readable', $file->getPathname()));
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
        $line = 0;

        foreach ($this->_file as $row) {
            $line++;
            $row = trim($row);
            if ('' === $row || in_array($row[0], array('#', '@'))) {
                continue;
            }
            $parts = preg_split('/\s+/', $row, 2, PREG_SPLIT_NO_EMPTY);
            if (count($parts) != 2) {
                throw new \ErrorException(sprintf('Invalid row on line %d has %d parts, expected 2', $line, count($row)));
            }
            $map[] = $parts;
        }
        return $map;
    }
}
