<?php
/**
 * Object manager definition factory
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
 * @copyright {@copyright}
 * @license   {@license_link}
 *
 */
namespace Magento\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefinitionFactory
{
    /**
     * Directory containig compiled class metadata
     *
     * @var string
     */
    protected $_definitionDir;

    /**
     * Class generation dir
     *
     * @var string
     */
    protected $_generationDir;

    /**
     * Format of definitions
     *
     * @var string
     */
    protected $_definitionFormat;

    /**
     * Filesystem Driver
     *
     * @var \Magento\Filesystem\DriverInterface
     */
    protected $_filesystemDriver;

    /**
     * List of defintion models
     *
     * @var array
     */
    protected $_definitionClasses = array(
        'igbinary' => 'Magento\ObjectManager\Definition\Compiled\Binary',
        'serialized' => 'Magento\ObjectManager\Definition\Compiled\Serialized'
    );

    /**
     * @param \Magento\Filesystem\DriverInterface $filesystemDriver
     * @param string $definitionDir
     * @param string $generationDir
     * @param string  $definitionFormat
     */
    public function __construct(
        \Magento\Filesystem\DriverInterface $filesystemDriver,
        $definitionDir,
        $generationDir,
        $definitionFormat
    ) {
        $this->_filesystemDriver = $filesystemDriver;
        $this->_definitionDir = $definitionDir;
        $this->_generationDir = $generationDir;
        $this->_definitionFormat = $definitionFormat;
    }

    /**
     * @param $definitions
     * @return \Magento\ObjectManager\Definition\Runtime
     */
    public function createClassDefinition($definitions)
    {
        if (!$definitions) {
            $path = $this->_definitionDir . '/definitions.php';
            if ($this->_filesystemDriver->isReadable($path)) {
                $definitions = $this->_filesystemDriver->fileGetContents($path);
            }
        }
        if ($definitions) {
            if (is_string($definitions)) {
                $definitions = $this->_unpack($definitions);
            }
            $definitionModel = $this->_definitionClasses[$this->_definitionFormat];
            $result = new $definitionModel($definitions);
        } else {
            $autoloader = new \Magento\Autoload\IncludePath();
            $generatorIo = new \Magento\Code\Generator\Io(
                $this->_filesystemDriver,
                $autoloader,
                $this->_generationDir
            );
            $generator = new \Magento\Code\Generator(null, $autoloader, $generatorIo);
            $autoloader = new \Magento\Code\Generator\Autoloader($generator);
            spl_autoload_register(array($autoloader, 'load'));

            $result =  new \Magento\ObjectManager\Definition\Runtime();
        }
        return $result;
    }

    /**
     * Create plugin definitions
     *
     * @return \Magento\Interception\Definition
     */
    public function createPluginDefinition()
    {
        $path = $this->_definitionDir . '/plugins.php';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\Interception\Definition\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\Interception\Definition\Runtime();
        }
    }

    /**
     * @return \Magento\ObjectManager\Relations
     */
    public function createRelations()
    {
        $path = $this->_definitionDir . '/' . 'relations.php';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\ObjectManager\Relations\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\ObjectManager\Relations\Runtime();
        }
    }

    /**
     * Uncompress definitions
     *
     * @param string $definitions
     * @return mixed
     */
    protected function _unpack($definitions)
    {
        $extractor = $this->_definitionFormat == 'igbinary' ? 'igbinary_unserialize' : 'unserialize';
        return $extractor($definitions);
    }
}
