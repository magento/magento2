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

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends TestCase
{
    const CLASS_NAME_WITH_NAMESPACE = GeneratorTest\SourceClassWithNamespace::class;

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
     * Generates a new file with Factory class and compares with the sample from the
     * SourceClassWithNamespaceFactory.php.sample file.
     */
    public function testGenerateClassFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Factory';
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($factoryClassName));
        $factory = Bootstrap::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $factory->create());
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($factoryClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceFactory.php.sample')
        );
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * Generates a new file with Proxy class and compares with the sample from the
     * SourceClassWithNamespaceProxy.php.sample file.
     */
    public function testGenerateClassProxyWithNamespace()
    {
        $proxyClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Proxy';
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($proxyClassName));
        $proxy = Bootstrap::getObjectManager()->create($proxyClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $proxy);
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($proxyClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceProxy.php.sample')
        );
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * Generates a new file with Interceptor class and compares with the sample from the
     * SourceClassWithNamespaceInterceptor.php.sample file.
     */
    public function testGenerateClassInterceptorWithNamespace()
    {
        $interceptorClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Interceptor';
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($interceptorClassName));
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($interceptorClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceInterceptor.php.sample')
        );
        $this->assertEquals($expectedContent, $content);
    }

    /**
     * Generates a new file with ExtensionInterfaceFactory class and compares with the sample from the
     * SourceClassWithNamespaceExtensionInterfaceFactory.php.sample file.
     */
    public function testGenerateClassExtensionAttributesInterfaceFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'ExtensionInterfaceFactory';
        $this->generatedDirectory->create($this->testRelativePath);
        $this->assertEquals(Generator::GENERATION_SUCCESS, $this->_generator->generateClass($factoryClassName));
        $factory = Bootstrap::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE . 'Extension', $factory->create());
        $content = $this->_clearDocBlock(
            file_get_contents($this->_ioObject->generateResultFileName($factoryClassName))
        );
        $expectedContent = $this->_clearDocBlock(
            file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceExtensionInterfaceFactory.php.sample')
        );
        $this->assertEquals($expectedContent, $content);
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
        $this->generatedDirectory->create($this->testRelativePath);
        $this->generatedDirectory->changePermissionsRecursively($this->testRelativePath, 0555, 0444);
        $generatorResult = $this->_generator->generateClass($factoryClassName);
        $this->assertFalse($generatorResult);
        $pathToSystemLog = $this->logDirectory->getAbsolutePath('system.log');
        $this->assertStringContainsString($msgPart, file_get_contents($pathToSystemLog));
    }
}
