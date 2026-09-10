<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config\Reader;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Config\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ConfigDomMock.php';

class DomTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $fileResolverMock;

    /**
     * @var MockObject
     */
    protected $converterMock;

    /**
     * @var MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var MockObject
     */
    protected $validationStateMock;

    /**
     * @var Dom
     */
    protected $model;

    protected function setUp(): void
    {
        $this->fileResolverMock = $this->createMock(FileResolverInterface::class);
        $this->converterMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class);
        $this->schemaLocatorMock = $this->createMock(SchemaLocator::class);
        $this->validationStateMock = $this->createMock(ValidationStateInterface::class);

        $this->model = new Dom(
            $this->fileResolverMock,
            $this->converterMock,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            'filename.xml',
            [],
            '\ConfigDomMock'
        );
    }

    /**
     * @covers \Magento\Framework\ObjectManager\Config\Reader\Dom::_createConfigMerger()
     */
    public function testRead()
    {
        $fileList = ['first content item'];
        $this->fileResolverMock->expects($this->once())->method('get')->willReturn($fileList);
        $this->converterMock->expects($this->once())->method('convert')->with('reader dom result');
        $this->model->read();
    }

    /**
     * Repeated reads of one scope must parse the configuration only once.
     */
    public function testReadParsesEachScopeOnce()
    {
        $this->fileResolverMock->expects($this->once())->method('get')->willReturn(['first content item']);
        $this->converterMock->expects($this->once())->method('convert')->willReturn(['converted']);

        $first = $this->model->read('global');
        $second = $this->model->read('global');

        $this->assertSame(['converted'], $first);
        $this->assertSame($first, $second);
    }

    /**
     * read() and read($defaultScope) address the same scope and must share one parse.
     */
    public function testReadNormalizesTheDefaultScope()
    {
        $model = new Dom(
            $this->fileResolverMock,
            $this->converterMock,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            'filename.xml',
            [],
            '\ConfigDomMock',
            'frontend'
        );
        $this->fileResolverMock->expects($this->once())
            ->method('get')
            ->with('filename.xml', 'frontend')
            ->willReturn(['first content item']);
        $this->converterMock->expects($this->once())->method('convert')->willReturn(['converted']);

        $this->assertSame(['converted'], $model->read());
        $this->assertSame(['converted'], $model->read('frontend'));
    }

    /**
     * Two readers may be configured differently, so one must never serve the other's result.
     */
    public function testReadIsNotSharedBetweenInstances()
    {
        $otherResolver = $this->createMock(FileResolverInterface::class);
        $otherConverter = $this->createMock(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class);
        $other = new Dom(
            $otherResolver,
            $otherConverter,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            'filename.xml',
            [],
            '\ConfigDomMock'
        );

        $this->fileResolverMock->expects($this->once())->method('get')->willReturn(['first content item']);
        $this->converterMock->expects($this->once())->method('convert')->willReturn(['from first reader']);
        $otherResolver->expects($this->once())->method('get')->willReturn(['first content item']);
        $otherConverter->expects($this->once())->method('convert')->willReturn(['from second reader']);

        $this->assertSame(['from first reader'], $this->model->read('global'));
        $this->assertSame(['from second reader'], $other->read('global'));
    }

    /**
     * A long-running process must not carry one request's parsed configuration into the next.
     */
    public function testResetStateDropsTheMemoizedScopes()
    {
        $this->fileResolverMock->expects($this->exactly(2))->method('get')->willReturn(['first content item']);
        $this->converterMock->expects($this->exactly(2))->method('convert')->willReturn(['converted']);

        $this->assertSame(['converted'], $this->model->read('global'));
        $this->model->_resetState();
        $this->assertSame(['converted'], $this->model->read('global'), 'the scope should be parsed again');
    }
}
