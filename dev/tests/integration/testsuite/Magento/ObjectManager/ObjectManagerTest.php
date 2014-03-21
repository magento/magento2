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
 * @package     Magento_ObjectManager
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test classes for basic instantiation
     */
    const TEST_CLASS = 'Magento\ObjectManager\TestAsset\Basic';

    const TEST_CLASS_INJECTION = 'Magento\ObjectManager\TestAsset\BasicInjection';

    /**#@-*/

    /**#@+
     * Test classes and interface to test preferences
     */
    const TEST_INTERFACE = 'Magento\ObjectManager\TestAsset\TestAssetInterface';

    const TEST_INTERFACE_IMPLEMENTATION = 'Magento\ObjectManager\TestAsset\InterfaceImplementation';

    const TEST_CLASS_WITH_INTERFACE = 'Magento\ObjectManager\TestAsset\InterfaceInjection';

    /**#@-*/

    /**
     * @var \Magento\ObjectManager
     */
    protected static $_objectManager;

    /**
     * List of classes with different number of arguments
     *
     * @var array
     */
    protected $_numerableClasses = array(
        0 => 'Magento\ObjectManager\TestAsset\ConstructorNoArguments',
        1 => 'Magento\ObjectManager\TestAsset\ConstructorOneArgument',
        2 => 'Magento\ObjectManager\TestAsset\ConstructorTwoArguments',
        3 => 'Magento\ObjectManager\TestAsset\ConstructorThreeArguments',
        4 => 'Magento\ObjectManager\TestAsset\ConstructorFourArguments',
        5 => 'Magento\ObjectManager\TestAsset\ConstructorFiveArguments',
        6 => 'Magento\ObjectManager\TestAsset\ConstructorSixArguments',
        7 => 'Magento\ObjectManager\TestAsset\ConstructorSevenArguments',
        8 => 'Magento\ObjectManager\TestAsset\ConstructorEightArguments',
        9 => 'Magento\ObjectManager\TestAsset\ConstructorNineArguments',
        10 => 'Magento\ObjectManager\TestAsset\ConstructorTenArguments'
    );

    /**
     * Names of properties
     *
     * @var array
     */
    protected $_numerableProperties = array(
        1 => '_one',
        2 => '_two',
        3 => '_three',
        4 => '_four',
        5 => '_five',
        6 => '_six',
        7 => '_seven',
        8 => '_eight',
        9 => '_nine',
        10 => '_ten'
    );

    public static function setUpBeforeClass()
    {
        $config = new \Magento\ObjectManager\Config\Config();

        $dirList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\App\Filesystem\DirectoryList'
        );
        $loader = new \Magento\App\Arguments\Loader($dirList);
        $arguments = new \Magento\App\Arguments(array(), $loader);
        $const = new \Magento\Data\Argument\Interpreter\Constant();
        $argInterpreter = new \Magento\App\Arguments\ArgumentInterpreter($arguments, $const);
        $argObjectFactory = new \Magento\ObjectManager\Config\Argument\ObjectFactory($config);
        $factory = new \Magento\ObjectManager\Factory\Factory($config, $argInterpreter, $argObjectFactory);

        self::$_objectManager = new \Magento\ObjectManager\ObjectManager($factory, $config);
        self::$_objectManager->configure(
            array('preferences' => array(self::TEST_INTERFACE => self::TEST_INTERFACE_IMPLEMENTATION))
        );
        $argObjectFactory->setObjectManager(self::$_objectManager);
    }

    public static function tearDownAfterClass()
    {
        self::$_objectManager = null;
    }

    /**
     * Data provider for testNewInstance
     *
     * @return array
     */
    public function newInstanceDataProvider()
    {
        $data = array(
            'basic model' => array(
                '$actualClassName' => self::TEST_CLASS_INJECTION,
                '$properties' => array('_object' => self::TEST_CLASS)
            ),
            'model with interface' => array(
                '$actualClassName' => self::TEST_CLASS_WITH_INTERFACE,
                '$properties' => array('_object' => self::TEST_INTERFACE_IMPLEMENTATION)
            )
        );

        foreach ($this->_numerableClasses as $number => $className) {
            $properties = array();
            for ($i = 1; $i <= $number; $i++) {
                $propertyName = $this->_numerableProperties[$i];
                $properties[$propertyName] = self::TEST_CLASS;
            }
            $data[$number . ' arguments'] = array('$actualClassName' => $className, '$properties' => $properties);
        }

        return $data;
    }

    /**
     * @param string $actualClassName
     * @param array $properties
     * @param string|null $expectedClassName
     *
     * @dataProvider newInstanceDataProvider
     */
    public function testNewInstance($actualClassName, array $properties = array(), $expectedClassName = null)
    {
        if (!$expectedClassName) {
            $expectedClassName = $actualClassName;
        }

        $testObject = self::$_objectManager->create($actualClassName);
        $this->assertInstanceOf($expectedClassName, $testObject);

        if ($properties) {
            foreach ($properties as $propertyName => $propertyClass) {
                $this->assertAttributeInstanceOf($propertyClass, $propertyName, $testObject);
            }
        }
    }
}
