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
 * @package     Magento_Code
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Code_Generator
{
    const GENERATION_SUCCESS = 'success';
    const GENERATION_ERROR = 'error';
    const GENERATION_SKIP = 'skip';

    /**
     * @var Magento_Code_Generator_EntityAbstract
     */
    protected $_generator;

    /**
     * @var Magento_Autoload_IncludePath
     */
    protected $_autoloader;

    /**
     * @var Magento_Code_Generator_Io
     */
    protected $_ioObject;

    /**
     * @var array
     */
    protected $_generatedEntities = array(
        Magento_Code_Generator_Factory::ENTITY_TYPE,
        Magento_Code_Generator_Proxy::ENTITY_TYPE,
        Magento_Code_Generator_Interceptor::ENTITY_TYPE,
    );

    /**
     * @param Magento_Code_Generator_EntityAbstract $generator
     * @param Magento_Autoload_IncludePath $autoloader
     * @param Magento_Code_Generator_Io $ioObject
     */
    public function __construct(
        Magento_Code_Generator_EntityAbstract $generator = null,
        Magento_Autoload_IncludePath $autoloader = null,
        Magento_Code_Generator_Io $ioObject = null
    ) {
        $this->_generator  = $generator;
        $this->_autoloader = $autoloader ? : new Magento_Autoload_IncludePath();
        $this->_ioObject   = $ioObject ? : new Magento_Code_Generator_Io(new Varien_Io_File(), $this->_autoloader);
    }

    /**
     * @return array
     */
    public function getGeneratedEntities()
    {
        return $this->_generatedEntities;
    }

    /**
     * @param string $className
     * @return string const
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
            return self::GENERATION_ERROR;
        }

        // check if file already exists
        $autoloader = $this->_autoloader;
        if ($autoloader::getFile($className)) {
            return self::GENERATION_SKIP;
        }

        // generate class file
        $this->_initGenerator($entity, $entityName, $className);
        if (!$this->_generator->generate()) {
            $errors = $this->_generator->getErrors();
            throw new Magento_Exception(implode(' ', $errors));
        }

        // remove generator
        $this->_generator = null;

        return self::GENERATION_SUCCESS;
    }

    /**
     * Get generator by entity type
     *
     * @param string $entity
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return Magento_Code_Generator_EntityAbstract|Magento_Code_Generator_Factory|Magento_Code_Generator_Proxy
     * @throws InvalidArgumentException
     */
    protected function _initGenerator($entity, $sourceClassName, $resultClassName)
    {
        if (!$this->_generator) {
            switch ($entity) {
                case Magento_Code_Generator_Factory::ENTITY_TYPE:
                    $this->_generator = new Magento_Code_Generator_Factory($sourceClassName, $resultClassName,
                        $this->_ioObject
                    );
                    break;
                case Magento_Code_Generator_Proxy::ENTITY_TYPE:
                    $this->_generator = new Magento_Code_Generator_Proxy($sourceClassName, $resultClassName,
                        $this->_ioObject
                    );
                    break;
                case Magento_Code_Generator_Interceptor::ENTITY_TYPE:
                    $this->_generator = new Magento_Code_Generator_Interceptor($sourceClassName, $resultClassName,
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
