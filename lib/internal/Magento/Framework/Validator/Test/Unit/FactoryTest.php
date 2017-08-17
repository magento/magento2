<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Test\Unit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Validator\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorConfigMock;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileIteratorFactoryMock;

    /**
     * @var \Magento\Framework\Config\FileIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileIteratorMock;

    /**
     * @var \Magento\Framework\Translate\AdapterInterface
     */
    private $defaultTranslator;

    /**
     * @var \Magento\Framework\Validator\Factory
     */
    private $factory;

    /**
     * @var string
     */
    private $jsonString = '["\/tmp\/moduleOne\/etc\/validation.xml"]';

    /**
     * @var array
     */
    private $data = ['/tmp/moduleOne/etc/validation.xml'];

    protected function setUp()
    {
        $this->defaultTranslator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->validatorConfigMock = $this->createPartialMock(
            \Magento\Framework\Validator\Config::class,
            ['createValidatorBuilder', 'createValidator']
        );
        $translateAdapterMock = $this->createMock(\Magento\Framework\Translate\Adapter::class);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Framework\Translate\Adapter::class)
            ->willReturn($translateAdapterMock);
        $this->fileIteratorMock = $this->createMock(\Magento\Framework\Config\FileIterator::class);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(
                \Magento\Framework\Validator\Config::class,
                ['configFiles' => $this->fileIteratorMock]
            )
            ->willReturn($this->validatorConfigMock);
        $this->readerMock = $this->createPartialMock(
            \Magento\Framework\Module\Dir\Reader::class,
            ['getConfigurationFiles']
        );
        $this->cacheMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->factory = $objectManager->getObject(
            \Magento\Framework\Validator\Factory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'moduleReader' => $this->readerMock,
                'cache' => $this->cacheMock
            ]
        );

        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->fileIteratorFactoryMock = $this->createMock(\Magento\Framework\Config\FileIteratorFactory::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->factory,
            'serializer',
            $this->serializerMock
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->factory,
            'fileIteratorFactory',
            $this->fileIteratorFactoryMock
        );
    }

    /**
     * Restore default translator
     */
    protected function tearDown()
    {
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($this->defaultTranslator);
        unset($this->defaultTranslator);
    }

    public function testGetValidatorConfig()
    {
        $this->readerMock->method('getConfigurationFiles')
            ->with('validation.xml')
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->method('toArray')
            ->willReturn($this->data);
        $actualConfig = $this->factory->getValidatorConfig();
        $this->assertInstanceOf(
            \Magento\Framework\Validator\Config::class,
            $actualConfig,
            'Object of incorrect type was created'
        );
        $this->assertInstanceOf(
            \Magento\Framework\Translate\Adapter::class,
            \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator(),
            'Default validator translate adapter was not set correctly'
        );
    }

    public function testGetValidatorConfigCacheNotExist()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->readerMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->method('toArray')
            ->willReturn($this->data);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with($this->jsonString);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->data)
            ->willReturn($this->jsonString);
        $this->factory->getValidatorConfig();
        $this->factory->getValidatorConfig();
    }

    public function testGetValidatorConfigCacheExist()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($this->jsonString);
        $this->readerMock->expects($this->never())
            ->method('getConfigurationFiles');
        $this->cacheMock->expects($this->never())
            ->method('save');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($this->jsonString)
            ->willReturn($this->data);
        $this->fileIteratorFactoryMock->method('create')
            ->willReturn($this->fileIteratorMock);
        $this->factory->getValidatorConfig();
        $this->factory->getValidatorConfig();
    }

    public function testCreateValidatorBuilder()
    {
        $this->readerMock->method('getConfigurationFiles')
            ->with('validation.xml')
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->method('toArray')
            ->willReturn($this->data);
        $builderMock = $this->createMock(\Magento\Framework\Validator\Builder::class);
        $this->validatorConfigMock->expects($this->once())
            ->method('createValidatorBuilder')
            ->with('test', 'class', [])
            ->willReturn($builderMock);
        $this->assertInstanceOf(
            \Magento\Framework\Validator\Builder::class,
            $this->factory->createValidatorBuilder('test', 'class', [])
        );
    }

    public function testCreateValidator()
    {
        $this->readerMock->method('getConfigurationFiles')
            ->with('validation.xml')
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->method('toArray')
            ->willReturn($this->data);
        $validatorMock = $this->createMock(\Magento\Framework\Validator::class);
        $this->validatorConfigMock->expects($this->once())
            ->method('createValidator')
            ->with('test', 'class', [])
            ->willReturn($validatorMock);
        $this->assertInstanceOf(
            \Magento\Framework\Validator::class,
            $this->factory->createValidator('test', 'class', [])
        );
    }
}
