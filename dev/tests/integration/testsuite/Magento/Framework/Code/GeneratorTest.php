<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceFactoryGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;
use Magento\Framework\ObjectManager\Code\Generator as DIGenerator;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/GeneratorTest/SourceClassWithNamespace.php';
require_once __DIR__ . '/GeneratorTest/ParentClassWithNamespace.php';
require_once __DIR__ . '/GeneratorTest/SourceClassWithNamespaceExtension.php';
require_once __DIR__ . '/GeneratorTest/NestedNamespace/SourceClassWithNestedNamespace.php';
require_once __DIR__ . '/GeneratorTest/NestedNamespace/SourceClassWithNestedNamespaceExtension.php';

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends TestCase
{
    const CLASS_NAME_WITH_NAMESPACE = GeneratorTest\SourceClassWithNamespace::class;
    const CLASS_NAME_WITH_NESTED_NAMESPACE = GeneratorTest\NestedNamespace\SourceClassWithNestedNamespace::class;
    const EXTENSION_CLASS_NAME_WITH_NAMESPACE = GeneratorTest\SourceClassWithNamespaceExtension::class;
    const EXTENSION_CLASS_NAME_WITH_NESTED_NAMESPACE =
        GeneratorTest\NestedNamespace\SourceClassWithNestedNamespaceExtension::class;

    /**
     * @var Generator
     */
    protected $_generator;

    /**
     * @var Generator/Io
     */
    protected $_ioObject;

    /**
     * @var Filesystem\Directory\Write
     */
    private $generatedDirectory;

    /**
     * @var Filesystem\Directory\Read
     */
    private $logDirectory;

    /**
     * @var string
     */
    private $testRelativePath = './Magento/Framework/Code/GeneratorTest/';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->get(Filesystem::class);
        $this->generatedDirectory = $filesystem->getDirectoryWrite(DirectoryList::GENERATED_CODE);
        $this->generatedDirectory->create($this->testRelativePath);
        $this->logDirectory = $filesystem->getDirectoryRead(DirectoryList::LOG);
        $generatedDirectoryAbsolutePath = $this->generatedDirectory->getAbsolutePath();
        $this->_ioObject = new Generator\Io(new Filesystem\Driver\File(), $generatedDirectoryAbsolutePath);
        $this->_generator = $objectManager->create(
            Generator::class,
            [
                'ioObject' => $this->_ioObject,
                'generatedEntities' => [
                    ExtensionAttributesInterfaceFactoryGenerator::ENTITY_TYPE =>
                        ExtensionAttributesInterfaceFactoryGenerator::class,
                    DIGenerator\Factory::ENTITY_TYPE => DIGenerator\Factory::class,
                    DIGenerator\Proxy::ENTITY_TYPE => DIGenerator\Proxy::class,
                    InterceptionGenerator\Interceptor::ENTITY_TYPE => InterceptionGenerator\Interceptor::class,
                ]
            ]
        );
        $this->_generator->setObjectManager($objectManager);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_generator = null;
        if ($this->generatedDirectory->isExist($this->testRelativePath)) {
            if (!$this->generatedDirectory->isWritable($this->testRelativePath)) {
                $this->generatedDirectory->changePermissionsRecursively($this->testRelativePath, 0775, 0664);
            }
            $this->generatedDirectory->delete($this->testRelativePath);
        }
    }

    protected function _clearDocBlock($classBody)
    {
        return preg_replace('/(\/\*[\w\W]*)\nclass/', 'class', $classBody);
    }

    /**
     * Generates a new class Factory file and compares with the sample.
     *
     * @param $className
     * @param $generateType
     * @param $expectedDataPath
     * @dataProvider generateClassFactoryDataProvider
     */
    public function testGenerateClassFactory($className, $generateType, $expectedDataPath)
    {
        $factoryClassName = $className . $generateType;
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($factoryClassName));
        $factory = Bootstrap::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf($className, $factory->create());
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($factoryClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . $expectedDataPath)
        );
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * DataProvider for testGenerateClassFactory
     *
     * @return array
     */
    public static function generateClassFactoryDataProvider()
    {
        return [
            'factory_with_namespace' => [
                'className' => self::CLASS_NAME_WITH_NAMESPACE,
                'generateType' => 'Factory',
                'expectedDataPath' => '/_expected/SourceClassWithNamespaceFactory.php.sample'
            ],
            'factory_with_nested_namespace' => [
                'className' => self::CLASS_NAME_WITH_NESTED_NAMESPACE,
                'generateType' => 'Factory',
                'expectedDataPath' => '/_expected/SourceClassWithNestedNamespaceFactory.php.sample'
            ],
            'ext_interface_factory_with_namespace' => [
                'className' => self::EXTENSION_CLASS_NAME_WITH_NAMESPACE,
                'generateType' => 'InterfaceFactory',
                'expectedDataPath' => '/_expected/SourceClassWithNamespaceExtensionInterfaceFactory.php.sample'
            ],
            'ext_interface_factory_with_nested_namespace' => [
                'className' => self::EXTENSION_CLASS_NAME_WITH_NESTED_NAMESPACE,
                'generateType' => 'InterfaceFactory',
                'expectedDataPath' => '/_expected/SourceClassWithNestedNamespaceExtensionInterfaceFactory.php.sample'
            ],
        ];
    }

    /**
     * @param $className
     * @param $generateType
     * @param $expectedDataPath
     * @dataProvider generateClassDataProvider
     */
    public function testGenerateClass($className, $generateType, $expectedDataPath)
    {
        $generateClassName = $className . $generateType;
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($generateClassName));
        $instance = Bootstrap::getObjectManager()->create($generateClassName);
        $this->assertInstanceOf($className, $instance);
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($generateClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . $expectedDataPath)
        );
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * DataProvider for testGenerateClass
     *
     * @return array
     */
    public static function generateClassDataProvider()
    {
        return [
            'proxy' => [
                'className' => self::CLASS_NAME_WITH_NAMESPACE,
                'generateType' => '\Proxy',
                'expectedDataPath' => '/_expected/SourceClassWithNamespaceProxy.php.sample'
            ],
            'interceptor' => [
                'className' => self::CLASS_NAME_WITH_NAMESPACE,
                'generateType' => '\Interceptor',
                'expectedDataPath' => '/_expected/SourceClassWithNamespaceInterceptor.php.sample'
            ]
        ];
    }

    /**
     * It tries to generate a new class file when the generated directory is read-only
     */
    public function testGeneratorClassWithErrorSaveClassFile()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Factory';
        $msgPart = 'Class ' . $factoryClassName . ' generation error: The requested class did not generate properly, '
            . 'because the \'generated\' directory permission is read-only.';
        $regexpMsgPart = preg_quote($msgPart);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches("/.*$regexpMsgPart.*/");
        $this->generatedDirectory->changePermissionsRecursively($this->testRelativePath, 0555, 0444);
        $generatorResult = $this->_generator->generateClass($factoryClassName);
        $this->assertFalse($generatorResult);
        $pathToSystemLog = $this->logDirectory->getAbsolutePath('system.log');
        $this->assertContains($msgPart, file_get_contents($pathToSystemLog));
    }
}
