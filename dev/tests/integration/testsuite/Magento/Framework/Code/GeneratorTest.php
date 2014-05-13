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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Code;

use Magento\Framework\ObjectManager\Code\Generator as DIGenerator;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;

require_once __DIR__ . '/GeneratorTest/SourceClassWithNamespace.php';
require_once __DIR__ . '/GeneratorTest/ParentClassWithNamespace.php';
/**
 * @magentoAppIsolation enabled
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME_WITHOUT_NAMESPACE = 'Magento\Framework\Code\GeneratorTest\SourceClassWithoutNamespace';

    const CLASS_NAME_WITH_NAMESPACE = 'Magento\Framework\Code\GeneratorTest\SourceClassWithNamespace';

    const INTERFACE_NAME_WITHOUT_NAMESPACE = 'Magento\Framework\Code\GeneratorTest\SourceInterfaceWithoutNamespace';

    /**
     * @var string
     */
    protected $_includePath;

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
        $this->_includePath = get_include_path();

        $this->varDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Filesystem'
        )->getDirectoryWrite(
            \Magento\Framework\App\Filesystem::VAR_DIR
        );
        $generationDirectory = $this->varDirectory->getAbsolutePath('generation');

        (new \Magento\Framework\Autoload\IncludePath())->addIncludePath($generationDirectory);

        $this->_ioObject = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            new \Magento\Framework\Autoload\IncludePath(),
            $generationDirectory
        );
        $this->_generator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Code\Generator',
            array(
                'ioObject' => $this->_ioObject,
                'generatedEntities' => array(
                    DIGenerator\Factory::ENTITY_TYPE => '\Magento\Framework\ObjectManager\Code\Generator\Factory',
                    DIGenerator\Proxy::ENTITY_TYPE => '\Magento\Framework\ObjectManager\Code\Generator\Proxy',
                    InterceptionGenerator\Interceptor::ENTITY_TYPE =>
                        '\Magento\Framework\Interception\Code\Generator\Interceptor'
                )
            )
        );
    }

    protected function tearDown()
    {
        $this->varDirectory->delete('generation');
        set_include_path($this->_includePath);
        unset($this->_generator);
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
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result);

        /** @var $factory \Magento\Framework\ObjectManager\Factory */
        $factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($factoryClassName);

        $object = $factory->create();
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $object);

        // This test is only valid if the factory created the object if Autoloader did not pick it up automatically
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents($this->_ioObject->getResultFileName(self::CLASS_NAME_WITH_NAMESPACE . 'Factory'))
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceFactory.php')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }

    public function testGenerateClassProxyWithNamespace()
    {
        $proxyClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Proxy';
        $result = false;
        $generatorResult = $this->_generator->generateClass($proxyClassName);
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result);

        $proxy = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($proxyClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $proxy);

        // This test is only valid if the factory created the object if Autoloader did not pick it up automatically
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents($this->_ioObject->getResultFileName(self::CLASS_NAME_WITH_NAMESPACE . '\Proxy'))
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceProxy.php')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }

    public function testGenerateClassInterceptorWithNamespace()
    {
        $interceptorClassName = self::CLASS_NAME_WITH_NAMESPACE . '\Interceptor';
        $result = false;
        $generatorResult = $this->_generator->generateClass($interceptorClassName);
        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $result = true;
        }
        $this->assertTrue($result);

        if (\Magento\Framework\Code\Generator::GENERATION_SUCCESS == $generatorResult) {
            $content = $this->_clearDocBlock(
                file_get_contents(
                    $this->_ioObject->getResultFileName(self::CLASS_NAME_WITH_NAMESPACE . '\Interceptor')
                )
            );
            $expectedContent = $this->_clearDocBlock(
                file_get_contents(__DIR__ . '/_expected/SourceClassWithNamespaceInterceptor.php')
            );
            $this->assertEquals($expectedContent, $content);
        }
    }
}
