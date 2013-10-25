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
namespace Magento\Core\Model\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefinitionFactory
{
    /**
     * Format of definitions
     *
     * @var string
     */
    protected $_definitionFormat;

    /**
     * @var \Magento\Core\Model\Config\Primary
     */
    protected $_config;

    /**
     * @param \Magento\Core\Model\Config\Primary $config
     */
    public function __construct(\Magento\Core\Model\Config\Primary $config)
    {
        $this->_config = $config;
        $this->_definitionFormat = $config->getDefinitionFormat();
    }

    /**
     * Get definition model name
     *
     * @param string $format
     * @return string
     */
    protected function _getDefinitionModel($format)
    {
        switch ($format) {
            case 'igbinary':
                return 'Magento\ObjectManager\Definition\Compiled\Binary';

            case 'serialized':
            default:
                return 'Magento\ObjectManager\Definition\Compiled\Serialized';
        }
    }

    /**
     * Create object manager definition reader based on configuration
     *
     * @return \Magento\ObjectManager\Definition
     */
    public function createClassDefinition()
    {
        \Magento\Profiler::start('di_definitions_create');
        $definitions = $this->_config->getParam('definitions', false);
        if (!$definitions) { // check whether definitions were provided as application init param
            $path = $this->_config->getDefinitionPath() . DIRECTORY_SEPARATOR . 'definitions.php';
            if (is_readable($path)) {
                $definitions = file_get_contents($path);
            }
        }
        if ($definitions) {
            if (is_string($definitions)) {
                $definitions = $this->_unpack($definitions);
            }
            $definitionModel = $this->_getDefinitionModel($this->_definitionFormat);
            $output = new $definitionModel($definitions);
        } else {
            $genDir = $this->_config->getDirectories()->getDir(\Magento\App\Dir::GENERATION);
            $autoloader = new \Magento\Autoload\IncludePath();
            $generatorIo = new \Magento\Code\Generator\Io(new \Magento\Io\File(), $autoloader, $genDir);
            $generator = new \Magento\Code\Generator\ClassGenerator(
                new \Magento\Code\Generator(null, $autoloader, $generatorIo)
            );
            $definition =  new \Magento\ObjectManager\Definition\Runtime();
            $output = new \Magento\Code\Generator\DefinitionDecorator($definition, $generator);
        }
        \Magento\Profiler::stop('di_definitions_create');
        return $output;
    }

    /**
     * Retrieve list of plugin definitions
     *
     * @return \Magento\Interception\Definition
     */
    public function createPluginDefinition()
    {
        $path = $this->_config->getDefinitionPath() . DIRECTORY_SEPARATOR . 'plugins.php';
        if (is_readable($path)) {
            return new \Magento\Interception\Definition\Compiled($this->_unpack(file_get_contents($path)));
        } else {
            return new \Magento\Interception\Definition\Runtime();
        }
    }

    /**
     * Retreive class relations list
     *
     * @return \Magento\Core\Model\ObjectManager\Relations|\Magento\ObjectManager\Relations\Runtime
     */
    public function createRelations()
    {
        $path = $this->_config->getDefinitionPath() . DIRECTORY_SEPARATOR . 'relations.php';
        if (is_readable($path)) {
            return new \Magento\Core\Model\ObjectManager\Relations($this->_unpack(file_get_contents($path)));
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
