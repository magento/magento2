<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Code\Generator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;
use Magento\Framework\ObjectManager\Code\Generator as DIGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceFactoryGenerator;
use Magento\TestFramework\Helper\Bootstrap;

require_once __DIR__ . '/GeneratorTest/SourceClassWithNamespace.php';
require_once __DIR__ . '/GeneratorTest/ParentClassWithNamespace.php';
require_once __DIR__ . '/GeneratorTest/SourceClassWithNamespaceExtension.php';

/**
 * @magentoAppIsolation enabled
 */
class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    const CLASS_NAME_WITH_NAMESPACE = \Magento\Framework\Code\GeneratorTest\SourceClassWithNamespace::class;

    /**
     * @var \Magento\Framework\Code\Generator
     */
    protected $_generator;

    /**
     * @var \Magento\Framework\Code\Generator\Io
     */
    protected $_ioObject;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $varDirectory;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->varDirectory = $objectManager->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $generationDirectory = $this->varDirectory->getAbsolutePath('generation');
        $this->_ioObject = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $generationDirectory
        );
        $this->_generator = $objectManager->create(
            \Magento\Framework\Code\Generator::class,
            [
                'ioObject' => $this->_ioObject,
                'generatedEntities' => [
                    ExtensionAttributesInterfaceFactoryGenerator::ENTITY_TYPE =>
                        ExtensionAttributesInterfaceFactoryGenerator::class,
                    DIGenerator\Factory::ENTITY_TYPE => \Magento\Framework\ObjectManager\Code\Generator\Factory::class,
                    DIGenerator\Proxy::ENTITY_TYPE => \Magento\Framework\ObjectManager\Code\Generator\Proxy::class,
                    InterceptionGenerator\Interceptor::ENTITY_TYPE =>
                        \Magento\Framework\Interception\Code\Generator\Interceptor::class,
                ]
            ]
        );
        $this->_generator->setObjectManager($objectManager);
    }

    protected function tearDown()
    {
        $this->varDirectory->delete('generation');
        $this->_generator = null;
    }

    protected function _clearDocBlock($classBody)
    {
        return preg_replace('/(\/\*[\w\W]*)\nclass/', 'class', $classBody);
    }

    public function testGenerateClassFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Factory';
        $result = false;
        $generatorResult = $this->_generator->generateClass($factoryClassName);
        if (\Magento\Framework\Code\Generator::GENERATION_ERROR !== $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result, 'Failed asserting that \'' . (string)$generatorResult . '\' equals \'success\'.');

        $factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($factoryClassName);

        $object = $factory->create();
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $object);

        // This test is only valid if the factory created the object if Autoloader did not pick it up automatically
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents(
                    $this->_ioObject->generateResultFileName(self::CLASS_NAME_WITH_NAMESPACE . 'Factory')
                )
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceFactory.php.sample')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }

    public function testGenerateClassProxyWithNamespace()
    {
        $proxyClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Proxy';
        $result = false;
        $generatorResult = $this->_generator->generateClass($proxyClassName);
        if (\Magento\Framework\Code\Generator::GENERATION_ERROR !== $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result, 'Failed asserting that \'' . (string)$generatorResult . '\' equals \'success\'.');

        $proxy = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($proxyClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $proxy);

        // This test is only valid if the factory created the object if Autoloader did not pick it up automatically
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents($this->_ioObject->generateResultFileName(self::CLASS_NAME_WITH_NAMESPACE . '\Proxy'))
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceProxy.php.sample')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }

    public function testGenerateClassInterceptorWithNamespace()
    {
        $interceptorClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Interceptor';
        $result = false;
        $generatorResult = $this->_generator->generateClass($interceptorClassName);
        if (\Magento\Framework\Code\Generator::GENERATION_ERROR !== $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result, 'Failed asserting that \'' . (string)$generatorResult . '\' equals \'success\'.');

        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents(
                    $this->_ioObject->generateResultFileName(self::CLASS_NAME_WITH_NAMESPACE . '\Interceptor')
                )
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceInterceptor.php.sample')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }

    /**
     * Generates a new file with ExtensionInterfaceFactory class and compares with the sample from the
     * SourceClassWithNamespaceExtensionInterfaceFactory.php.sample file.
     */
    public function testGenerateClassExtensionAttributesInterfaceFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'ExtensionInterfaceFactory';
        $this->varDirectory->create(
            $this->varDirectory->getAbsolutePath('generation') . '/Magento/Framework/Code/GeneratorTest/'
        );

        $generatorResult = $this->_generator->generateClass($factoryClassName);

        $factory = Bootstrap::getObjectManager()->create($factoryClassName);
        $object = $factory->create();

        $this->assertEquals($generatorResult, Generator::GENERATION_SUCCESS);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE . 'Extension', $object);

        $content = $this->_clearDocBlock(
            file_get_contents(
                $this->_ioObject->generateResultFileName(self::CLASS_NAME_WITH_NAMESPACE . 'ExtensionInterfaceFactory')
            )
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceExtensionInterfaceFactory.php.sample')
        );
        $this->assertEquals($expectedContent, $content);
    }
}
