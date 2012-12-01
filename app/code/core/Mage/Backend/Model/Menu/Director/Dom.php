<?php
/**
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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Menu_Director_Dom extends Mage_Backend_Model_Menu_DirectorAbstract
{
    /**
     * Extracted config data
     * @var array
     */
    protected $_extractedData = array();

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param DOMDocument $menuConfig
     * @param Magento_ObjectManager $factory
     * @param Mage_Core_Model_Logger $logger
     */
    public function __construct(
        DOMDocument $menuConfig,
        Magento_ObjectManager $factory,
        Mage_Core_Model_Logger $logger
    ) {
        parent::__construct($menuConfig, $factory);
        $this->_logger = $logger;
        $this->_extractData();
    }

    /**
     * Extract data from DOMDocument
     * @return Mage_Backend_Model_Menu_Director_Dom
     */
    protected function _extractData()
    {
        $attributeNamesList = array(
            'id',
            'title',
            'toolTip',
            'module',
            'sortOrder',
            'action',
            'parent',
            'resource',
            'dependsOnModule',
            'dependsOnConfig',
        );
        $xpath = new DOMXPath($this->_configModel);
        $nodeList = $xpath->query('/config/menu/*');
        for ($i = 0; $i < $nodeList->length; $i++) {
            $item = array();
            $node = $nodeList->item($i);
            $item['type'] = $node->nodeName;
            foreach ($attributeNamesList as $name) {
                if ($node->hasAttribute($name)) {
                    $item[$name] = $node->getAttribute($name);
                }
            }
            $this->_extractedData[] = $item;
        }
    }

    /**
     * Get data that were extracted from config storage
     * @return array
     */
    public function getExtractedData()
    {
        return $this->_extractedData;
    }

    /**
     * Get command object
     * @param array $data command params
     * @return Mage_Backend_Model_Menu_Builder_CommandAbstract
     */
    protected function _getCommand($data)
    {
        switch ($data['type']) {
            case 'update':
                $command = $this->_factory->create(
                    'Mage_Backend_Model_Menu_Builder_Command_Update',
                    array('data' => $data)
                );
                $this->_logger->logDebug(
                    sprintf('Update on item with id %s was processed', $command->getId()),
                    Mage_Backend_Model_Menu::LOGGER_KEY
                );
                break;

            case 'remove':
                $command = $this->_factory->create(
                    'Mage_Backend_Model_Menu_Builder_Command_Remove',
                    array('data' => $data)
                );
                $this->_logger->logDebug(
                    sprintf('Remove on item with id %s was processed', $command->getId()),
                    Mage_Backend_Model_Menu::LOGGER_KEY
                );
                break;

            default:
                $command = $this->_factory->create(
                    'Mage_Backend_Model_Menu_Builder_Command_Add',
                    array('data' => $data)
                );
                break;
        }
        return $command;
    }

    /**
     *
     * @param Mage_Backend_Model_Menu_Builder $builder
     * @throws InvalidArgumentException if invalid builder object
     * @return Mage_Backend_Model_Menu_DirectorAbstract
     */
    public function buildMenu(Mage_Backend_Model_Menu_Builder $builder)
    {
        foreach ($this->getExtractedData() as $data) {
            $command = $this->_getCommand($data);
            $builder->processCommand($command);
        }
        return $this;
    }
}
