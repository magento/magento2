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
 */
class Mage_Core_Model_ObjectManager_DefinitionFactory
{
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
     * @param Mage_Core_Model_Config_Primary $config
     * @return Magento_ObjectManager_Definition
     */
    public function create(Mage_Core_Model_Config_Primary $config)
    {
        Magento_Profiler::start('di_definitions_create');
        $definitions = $config->getParam('definitions', false);
        if (!$definitions) { // check whether definitions were provided as application init param
            $path = $config->getDefinitionPath();
            if (is_readable($path)) {
                $definitions = file_get_contents($path);
            }
        }
        if ($definitions) {
            $format = $config->getDefinitionFormat();
            if (is_string($definitions)) {
                $extractor = $format == 'igbinary' ? 'igbinary_unserialize' : 'unserialize';
                $definitions = $extractor($definitions);
            }
            $definitionModel = $this->_getDefinitionModel($format);
            $output = new $definitionModel($definitions);
        } else {
            $genDir = $config->getDirectories()->getDir(Mage_Core_Model_Dir::VAR_DIR) . '/generation';
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
}
