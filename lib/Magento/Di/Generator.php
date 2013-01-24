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
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Generator
{
    /**
     * @var Magento_Di_Generator_EntityAbstract
     */
    protected $_generator;

    /**
     * @var Magento_Autoload_IncludePath
     */
    protected $_autoloader;

    /**
     * @var Magento_Di_Generator_Io
     */
    protected $_ioObject;

    /**
     * @var array
     */
    protected $_generatedEntities = array(
        Magento_Di_Generator_Factory::ENTITY_TYPE,
        Magento_Di_Generator_Proxy::ENTITY_TYPE
    );

    /**
     * @param Magento_Di_Generator_EntityAbstract $generator
     * @param Magento_Autoload_IncludePath $autoloader
     * @param Magento_Di_Generator_Io $ioObject
     */
    public function __construct(
        Magento_Di_Generator_EntityAbstract $generator = null,
        Magento_Autoload_IncludePath $autoloader = null,
        Magento_Di_Generator_Io $ioObject = null
    ) {
        $this->_generator  = $generator;
        $this->_autoloader = $autoloader ? : new Magento_Autoload_IncludePath();
        $this->_ioObject   = $ioObject ? : new Magento_Di_Generator_Io(new Varien_Io_File(), $this->_autoloader);
    }

    /**
     * @return array
     */
    public function getGeneratedEntities()
    {
        return $this->_generatedEntities;
    }

    /**
     * @param $className
     * @return bool
     * @throws Magento_Exception
     */
    public function generateClass($className)
    {
        // check if source class a generated entity
        $entity = null;
        $entityName = null;
        foreach ($this->_generatedEntities as $entityType) {
            $entitySuffix = ucfirst($entityType);
            // if $className string ends on $entitySuffix substring
            if (strrpos($className, $entitySuffix) === strlen($className) - strlen($entitySuffix)) {
                $entity = $entityType;
                $entityName = rtrim(substr($className, 0, -1 * strlen($entitySuffix)), '_');
                break;
            }
        }
        if (!$entity || !$entityName) {
            return false;
        }

        // check if file already exists
        $autoloader = $this->_autoloader;
        if ($autoloader::getFile($className)) {
            return false;
        }

        // generate class file
        $this->_initGenerator($entity, $entityName, $className);
        if (!$this->_generator->generate()) {
            $errors = $this->_generator->getErrors();
            throw new Magento_Exception(implode(' ', $errors));
        }

        // remove generator
        $this->_generator = null;

        return true;
    }

    /**
     * Get generator by entity type
     *
     * @param string $entity
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return Magento_Di_Generator_EntityAbstract|Magento_Di_Generator_Factory|Magento_Di_Generator_Proxy
     * @throws InvalidArgumentException
     */
    protected function _initGenerator($entity, $sourceClassName, $resultClassName)
    {
        if (!$this->_generator) {
            switch ($entity) {
                case Magento_Di_Generator_Factory::ENTITY_TYPE:
                    $this->_generator = new Magento_Di_Generator_Factory($sourceClassName, $resultClassName,
                        $this->_ioObject
                    );
                    break;
                case Magento_Di_Generator_Proxy::ENTITY_TYPE:
                    $this->_generator = new Magento_Di_Generator_Proxy($sourceClassName, $resultClassName,
                        $this->_ioObject
                    );
                    break;
                default:
                    throw new InvalidArgumentException('Unknown generation entity.');
                    break;
            }
        }

        return $this->_generator;
    }
}
