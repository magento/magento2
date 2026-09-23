<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Block\Sri;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Csp\Block\Sri\Hashes;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for Hashes block
 */
class HashesTest extends TestCase
{
    /**
     * @var Hashes
     */
    private Hashes $block;

    /**
     * @var MockObject|HashResolverInterface
     */
    private MockObject $hashResolverMock;

    /**
     * @var MockObject|SerializerInterface
     */
    private MockObject $serializerMock;

    /**
     * @var MockObject|Context
     */
    private MockObject $contextMock;

    /**
     * @var MockObject|SubresourceIntegrityRepositoryPool
     */
    private MockObject $repositoryPoolMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->hashResolverMock = $this->createMock(HashResolverInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->block = new Hashes(
            $this->contextMock,
            [],
            $this->repositoryPoolMock,
            $this->serializerMock,
            $this->hashResolverMock,
            $this->loggerMock
        );
    }

    /**
     * Test getSerialized returns serialized hashes from resolver
     */
    public function testGetSerializedReturnsHashesFromResolver(): void
    {
        $hashes = [
            'https://example.com/static/js/file1.js' => 'sha256-abc123',
            'https://example.com/static/js/file2.js' => 'sha256-def456'
        ];

        $this->hashResolverMock->expects($this->once())
            ->method('getAllHashes')
            ->willReturn($hashes);

        $expectedJson = json_encode($hashes);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($hashes)
            ->willReturn($expectedJson);

        $result = $this->block->getSerialized();
        $this->assertEquals($expectedJson, $result);
    }

    /**
     * Test getSerialized with empty hashes
     */
    public function testGetSerializedWithEmptyHashes(): void
    {
        $this->hashResolverMock->expects($this->once())
            ->method('getAllHashes')
            ->willReturn([]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([])
            ->willReturn('{}');

        $result = $this->block->getSerialized();
        $this->assertEquals('{}', $result);
    }

    /**
     * Test getSerialized returns empty object on exception
     */
    public function testGetSerializedReturnsEmptyOnException(): void
    {
        $this->hashResolverMock->expects($this->once())
            ->method('getAllHashes')
            ->willThrowException(new \Exception('Test exception'));

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'SRI: Failed to retrieve hashes',
                ['exception' => 'Test exception']
            );

        $result = $this->block->getSerialized();
        $this->assertEquals('{}', $result);
    }

    /**
     * Test getSerialized handles serializer exception
     */
    public function testGetSerializedHandlesSerializerException(): void
    {
        $this->hashResolverMock->expects($this->once())
            ->method('getAllHashes')
            ->willReturn(['url' => 'hash']);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willThrowException(new \Exception('Serialization failed'));

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'SRI: Failed to retrieve hashes',
                ['exception' => 'Serialization failed']
            );

        $result = $this->block->getSerialized();
        $this->assertEquals('{}', $result);
    }
}
