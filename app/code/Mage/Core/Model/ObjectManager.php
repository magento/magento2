<?php
/**
 * Magento application object manager. Configures and application application
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_ObjectManager extends Magento_ObjectManager_ObjectManager
{
    /**
     * @var Magento_ObjectManager_Relations
     */
    protected $_compiledRelations;

    /**
     * @param Mage_Core_Model_Config_Primary $primaryConfig
     * @param Magento_ObjectManager_Config $config
     */
    public function __construct(
        Mage_Core_Model_Config_Primary $primaryConfig,
        Magento_ObjectManager_Config $config = null
    ) {
        $definitionFactory = new Mage_Core_Model_ObjectManager_DefinitionFactory($primaryConfig);
        $definitions = $definitionFactory->createClassDefinition($primaryConfig);
        $config = $config ?: new Magento_ObjectManager_Config_Config();

        $appMode = $primaryConfig->getParam(Mage::PARAM_MODE, Mage_Core_Model_App_State::MODE_DEFAULT);
        $classBuilder = ($appMode == Mage_Core_Model_App_State::MODE_DEVELOPER)
            ? new Magento_ObjectManager_Interception_ClassBuilder_Runtime()
            : new Magento_ObjectManager_Interception_ClassBuilder_General();

        $factory = new Magento_ObjectManager_Interception_FactoryDecorator(
            new Magento_ObjectManager_Factory_Factory($config, null, $definitions, $primaryConfig->getParams()),
            $config,
            null,
            $definitionFactory->createPluginDefinition($primaryConfig),
            $classBuilder
        );
        parent::__construct($factory, $config, array(
            'Mage_Core_Model_Config_Primary' => $primaryConfig,
            'Mage_Core_Model_Dir' => $primaryConfig->getDirectories(),
            'Mage_Core_Model_ObjectManager' => $this
        ));
        $primaryConfig->configure($this);
    }

    /**
     * Load di area
     *
     * @param string $areaCode
     * @param Mage_Core_Model_Config $config
     */
    public function loadArea($areaCode, Mage_Core_Model_Config $config)
    {
        $key = $areaCode . 'DiConfig';
        /** @var Mage_Core_Model_CacheInterface $cache */
        $cache = $this->get('Mage_Core_Model_Cache_Type_Config');
        $data = $cache->load($key);
        if ($data) {
            $this->_config = unserialize($data);
            $this->_factory->setConfig($this->_config);
        } else {
            $diNode = $config->getNode($areaCode . '/di');
            if ($diNode) {
                $this->_config->extend($diNode->asArray());
            }
            if ($this->_factory->getDefinitions() instanceof Magento_ObjectManager_Definition_Compiled) {
                if (!$this->_compiledRelations) {
                    $this->_compiledRelations = new Mage_Core_Model_ObjectManager_Relations(
                        $this->get('Mage_Core_Model_Dir')
                    );
                }
                $this->_config->setRelations($this->_compiledRelations);
                foreach ($this->_factory->getDefinitions()->getClasses() as $type) {
                    $this->_config->hasPlugins($type);
                }
                $cache->save(serialize($this->_config), $key);
            }
        }
    }
}
