<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Adapter;
use Magento\Framework\Translate\AdapterInterface;
use Magento\Framework\Validator;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\Builder;
use Magento\Framework\Validator\Config;
use Magento\Framework\Validator\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var Config|MockObject
     */
    private $validatorConfigMock;

    /**
     * @var FileIterator|MockObject
     */
    private $fileIteratorMock;

    /**
     * @var AdapterInterface
     */
    private $defaultTranslator;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var array
     */
    private $data = ['/tmp/moduleOne/etc/validation.xml'];

    protected function setUp(): void
    {
        $this->defaultTranslator = AbstractValidator::getDefaultTranslator();

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->validatorConfigMock = $this->createPartialMock(
            Config::class,
            ['createValidatorBuilder', 'createValidator']
        );
        $translateAdapterMock = $this->createMock(Adapter::class);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(Adapter::class)
            ->willReturn($translateAdapterMock);
        $this->fileIteratorMock = $this->createMock(FileIterator::class);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(
                Config::class,
                ['configFiles' => $this->fileIteratorMock]
            )
            ->willReturn($this->validatorConfigMock);
        $this->readerMock = $this->createPartialMock(
            Reader::class,
            ['getConfigurationFiles']
        );
        $this->cacheMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $objectManager = new ObjectManager($this);

        $this->factory = $objectManager->getObject(
            Factory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'moduleReader' => $this->readerMock
            ]
        );
    }

    /**
     * Restore default translator
     */
    protected function tearDown(): void
    {
        AbstractValidator::setDefaultTranslator($this->defaultTranslator);
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
            Config::class,
            $actualConfig,
            'Object of incorrect type was created'
        );
        $this->assertInstanceOf(
            Adapter::class,
            AbstractValidator::getDefaultTranslator(),
            'Default validator translate adapter was not set correctly'
        );
    }

    public function testCreateValidatorBuilder()
    {
        $this->readerMock->method('getConfigurationFiles')
            ->with('validation.xml')
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->method('toArray')
            ->willReturn($this->data);
        $builderMock = $this->createMock(Builder::class);
        $this->validatorConfigMock->expects($this->once())
            ->method('createValidatorBuilder')
            ->with('test', 'class', [])
            ->willReturn($builderMock);
        $this->assertInstanceOf(
            Builder::class,
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
        $validatorMock = $this->createMock(Validator::class);
        $this->validatorConfigMock->expects($this->once())
            ->method('createValidator')
            ->with('test', 'class', [])
            ->willReturn($validatorMock);
        $this->assertInstanceOf(
            Validator::class,
            $this->factory->createValidator('test', 'class', [])
        );
    }
}
