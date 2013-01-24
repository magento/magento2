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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppIsolation enabled
 */
class Magento_Di_GeneratorTest extends PHPUnit_Framework_TestCase
{
    const CLASS_NAME_WITHOUT_NAMESPACE = 'Magento_Di_Generator_TestAsset_SourceClassWithoutNamespace';
    const CLASS_NAME_WITH_NAMESPACE = 'Magento\Di\Generator\TestAsset\SourceClassWithNamespace';

    /**
     * @var string
     */
    protected $_includePath;

    /**
     * @var Magento_Di_Generator
     */
    protected $_generator;

    protected function setUp()
    {
        $this->_includePath = get_include_path();

        /** @var $config Mage_Core_Model_Config */
        $config = Mage::getObjectManager()->get('Mage_Core_Model_Config');
        $generationDirectory = $config->getVarDir() . '/generation';

        Magento_Autoload_IncludePath::addIncludePath($generationDirectory);

        $ioObject = new Magento_Di_Generator_Io(
            new Varien_Io_File(),
            new Magento_Autoload_IncludePath(),
            $generationDirectory
        );
        $this->_generator = Mage::getObjectManager()->get('Magento_Di_Generator', array('ioObject' => $ioObject));
    }

    protected function tearDown()
    {
        /** @var $config Mage_Core_Model_Config */
        $config = Mage::getObjectManager()->get('Mage_Core_Model_Config');
        $generationDirectory = $config->getVarDir() . '/generation';
        Varien_Io_File::rmdirRecursive($generationDirectory);

        set_include_path($this->_includePath);
        unset($this->_generator);
    }

    public function testGenerateClassFactoryWithoutNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITHOUT_NAMESPACE . 'Factory';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        /** @var $factory Magento_ObjectManager_Factory */
        $factory = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf('Magento_ObjectManager_Factory', $factory);

        $object = $factory->createFromArray();
        $this->assertInstanceOf(self::CLASS_NAME_WITHOUT_NAMESPACE, $object);
    }

    public function testGenerateClassFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Factory';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        /** @var $factory Magento_ObjectManager_Factory */
        $factory = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf('Magento_ObjectManager_Factory', $factory);

        $object = $factory->createFromArray();
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $object);
    }

    public function testGenerateClassProxyWithoutNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITHOUT_NAMESPACE . 'Proxy';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        $proxy = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITHOUT_NAMESPACE, $proxy);

        $this->_verifyProxyMethods(self::CLASS_NAME_WITHOUT_NAMESPACE, $proxy);
    }

    public function testGenerateClassProxyWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Proxy';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        $proxy = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $proxy);

        $this->_verifyProxyMethods(self::CLASS_NAME_WITH_NAMESPACE, $proxy);
    }

    /**
     * @param string $class
     * @param object $proxy
     */
    protected function _verifyProxyMethods($class, $proxy)
    {
        $expectedMethods = array();
        $reflectionObject = new ReflectionClass(new $class());
        $publicMethods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic())) {
                $expectedMethods[$method->getName()] = $method->getParameters();
            }
        }

        $actualMethods = array();
        $reflectionObject = new ReflectionClass($proxy);
        $publicMethods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic())) {
                $actualMethods[$method->getName()] = $method->getParameters();
            }
        }

        $this->assertEquals($expectedMethods, $actualMethods);
    }
}
