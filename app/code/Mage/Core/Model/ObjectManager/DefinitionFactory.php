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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_ObjectManager_DefinitionFactory
{
    /**
     * Format of definitions
     *
     * @var string
     */
    protected $_definitionFormat;

    /**
     * @var Mage_Core_Model_Config_Primary
     */
    protected $_config;

    /**
     * @param Mage_Core_Model_Config_Primary $config
     */
    public function __construct(Mage_Core_Model_Config_Primary $config)
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
                return 'Magento_ObjectManager_Definition_Compiled_Binary';

            case 'serialized':
            default:
                return 'Magento_ObjectManager_Definition_Compiled_Serialized';
        }
    }

    /**
     * Create object manager definition reader based on configuration
     *
     * @return Magento_ObjectManager_Definition
     */
    public function createClassDefinition()
    {
        Magento_Profiler::start('di_definitions_create');
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
            $genDir = $this->_config->getDirectories()->getDir(Mage_Core_Model_Dir::GENERATION);
            $autoloader = new Magento_Autoload_IncludePath();
            $generatorIo = new Magento_Code_Generator_Io(new Varien_Io_File(), $autoloader, $genDir);
            $generator = new Magento_Code_Generator_Class(
                new Magento_Code_Generator(null, $autoloader, $generatorIo)
            );
            $definition =  new Magento_ObjectManager_Definition_Runtime();
            $output = new Magento_Code_Generator_DefinitionDecorator($definition, $generator);
        }
        Magento_Profiler::stop('di_definitions_create');
        return $output;
    }

    /**
     * Retrieve list of plugin definitions
     *
     * @return Magento_ObjectManager_Interception_Definition
     */
    public function createPluginDefinition()
    {
        $path = $this->_config->getDefinitionPath() . DIRECTORY_SEPARATOR . 'plugins.php';
        if (is_readable($path)) {
            return new Magento_ObjectManager_Interception_Definition_Compiled($this->_unpack(file_get_contents($path)));
        } else {
            return new Magento_ObjectManager_Interception_Definition_Runtime();
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
