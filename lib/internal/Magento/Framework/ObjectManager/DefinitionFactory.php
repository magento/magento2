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
namespace Magento\Framework\ObjectManager;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManager\Definition\Runtime;
use Magento\Framework\ObjectManager\Relations;
use Magento\Framework\ObjectManager\Code\Generator;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;
use Magento\Framework\Service\Code\Generator\Builder as BuilderGenerator;
use Magento\Framework\Service\Code\Generator\Mapper as MapperGenerator;
use Magento\Framework\ObjectManager\Code\Generator\Converter as ConverterGenerator;
use Magento\Framework\Service\Code\Generator\SearchResults;
use Magento\Framework\Service\Code\Generator\SearchResultsBuilder;
use Magento\Framework\ObjectManager\Profiler\Code\Generator as ProfilerGenerator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefinitionFactory
{
    /**
     * Directory containing compiled class metadata
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
     * @var DriverInterface
     */
    protected $_filesystemDriver;

    /**
     * List of definition models
     *
     * @var array
     */
    protected $_definitionClasses = array(
        'igbinary' => 'Magento\Framework\ObjectManager\Definition\Compiled\Binary',
        'serialized' => 'Magento\Framework\ObjectManager\Definition\Compiled\Serialized'
    );

    /**
     * @param DriverInterface $filesystemDriver
     * @param string $definitionDir
     * @param string $generationDir
     * @param string  $definitionFormat
     */
    public function __construct(DriverInterface $filesystemDriver, $definitionDir, $generationDir, $definitionFormat)
    {
        $this->_filesystemDriver = $filesystemDriver;
        $this->_definitionDir = $definitionDir;
        $this->_generationDir = $generationDir;
        $this->_definitionFormat = $definitionFormat;
    }

    /**
     * Create class definitions
     *
     * @param mixed $definitions
     * @param bool $useCompiled
     * @return Runtime
     */
    public function createClassDefinition($definitions, $useCompiled = true)
    {
        if (!$definitions && $useCompiled) {
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
            $autoloader = new \Magento\Framework\Autoload\IncludePath();
            $generatorIo = new \Magento\Framework\Code\Generator\Io(
                $this->_filesystemDriver,
                $autoloader,
                $this->_generationDir
            );
            $generator = new \Magento\Framework\Code\Generator(
                $autoloader,
                $generatorIo,
                array(

                    SearchResultsBuilder::ENTITY_TYPE
                        => '\Magento\Framework\Service\Code\Generator\SearchResultsBuilder',
                    Generator\Factory::ENTITY_TYPE
                        => '\Magento\Framework\ObjectManager\Code\Generator\Factory',
                    Generator\Proxy::ENTITY_TYPE
                        => '\Magento\Framework\ObjectManager\Code\Generator\Proxy',
                    Generator\Repository::ENTITY_TYPE
                        => '\Magento\Framework\ObjectManager\Code\Generator\Repository',
                    InterceptionGenerator\Interceptor::ENTITY_TYPE
                        => '\Magento\Framework\Interception\Code\Generator\Interceptor',
                    BuilderGenerator::ENTITY_TYPE
                        => '\Magento\Framework\Service\Code\Generator\Builder',
                    MapperGenerator::ENTITY_TYPE
                        => '\Magento\Framework\Service\Code\Generator\Mapper',
                    SearchResults::ENTITY_TYPE
                        => '\Magento\Framework\Service\Code\Generator\SearchResults',
                    ConverterGenerator::ENTITY_TYPE
                        => '\Magento\Framework\ObjectManager\Code\Generator\Converter',
                    ProfilerGenerator\Logger::ENTITY_TYPE
                        => '\Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger'
                )
            );
            $autoloader = new \Magento\Framework\Code\Generator\Autoloader($generator);
            spl_autoload_register(array($autoloader, 'load'));

            $result = new Runtime();
        }
        return $result;
    }

    /**
     * Create plugin definitions
     *
     * @return \Magento\Framework\Interception\Definition
     */
    public function createPluginDefinition()
    {
        $path = $this->_definitionDir . '/plugins.php';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\Framework\Interception\Definition\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\Framework\Interception\Definition\Runtime();
        }
    }

    /**
     * Create relations
     *
     * @return Relations
     */
    public function createRelations()
    {
        $path = $this->_definitionDir . '/' . 'relations.php';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\Framework\ObjectManager\Relations\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\Framework\ObjectManager\Relations\Runtime();
        }
    }

    /**
     * Un-compress definitions
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
