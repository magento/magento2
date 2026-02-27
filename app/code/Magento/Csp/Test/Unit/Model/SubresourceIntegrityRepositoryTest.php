<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrity\StorageInterface;

/**
 * Unit Test for Class @see Magento\Csp\Model\SubresourceIntegrityRepository
 *
 */
class SubresourceIntegrityRepositoryTest extends TestCase
{
    /**
     * @var string
     */
    private string $context = "test";

    /**
     * @var MockObject
     */
    private MockObject $cacheMock;

    /**
     * @var MockObject
     */
    private MockObject $serializerMock;

    /**
     * @var MockObject
     */
    private MockObject $storage;

    /**
     * @var MockObject
     */
    private MockObject $integrityFactoryMock;

    /**
     * @var SubresourceIntegrityRepository|null
     */
    private ?SubresourceIntegrityRepository $subresourceIntegrityRepository = null;

    /**
     * Initialize dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->integrityFactoryMock = $this->createMock(SubresourceIntegrityFactory::class);
        $this->storage = $this->createMock(StorageInterface::class);

        $this->subresourceIntegrityRepository = new SubresourceIntegrityRepository(
            $this->cacheMock,
            $this->serializerMock,
            $this->integrityFactoryMock,
            $this->context,
            $this->storage
        );
    }

    /**
     * Test save repository
     *
     * @return void
     */
    public function testSave(): void
    {
        $data = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'path' => 'js/jquery.js'
            ]
        );

        $expected[$data->getPath()] = $data->getHash();
        $serialized = json_encode($expected);

        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn(null);

        $this->serializerMock->expects($this->never())
            ->method('unserialize');

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expected)
            ->willReturn($serialized);

        $this->storage->expects($this->once())
            ->method('save')
            ->with($serialized, $this->context)
            ->willReturn(true);

        $this->assertTrue(
            $this->subresourceIntegrityRepository->save($data)
        );
    }

    /**
     * Test that cache saves in bunch
     *
     * @return void
     */
    public function testSaveBunch(): void
    {
        $bunch = [
            new SubresourceIntegrity(
                [
                    'hash' => 'testhash',
                    'path' => 'js/jquery.js'
                ]
            ),
            new SubresourceIntegrity(
                [
                    'hash' => 'testhash2',
                    'path' => 'js/test.js'
                ]
            )
        ];

        $expected = [];

        foreach ($bunch as $integrity) {
            $expected[$integrity->getPath()] = $integrity->getHash();
        }

        $serializedBunch = json_encode($expected);

        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn(null);

        $this->serializerMock->expects($this->never())
            ->method('unserialize');

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expected)
            ->willReturn($serializedBunch);

        $this->storage->expects($this->once())
            ->method('save')
            ->with($serializedBunch, $this->context)
            ->willReturn(true);

        $this->assertTrue(
            $this->subresourceIntegrityRepository->saveBunch($bunch)
        );
    }

    /**
     * Test that getByPath returns null when storage data is corrupted and safeRemove succeeds
     *
     * @return void
     */
    public function testGetByPathWithCorruptedDataAndSuccessfulRemove(): void
    {
        $corruptedData = 'invalid-json-data';

        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn($corruptedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($corruptedData)
            ->willThrowException(new \InvalidArgumentException('Invalid data'));

        // safeRemove should be called and succeed
        $this->storage->expects($this->once())
            ->method('remove')
            ->with($this->context)
            ->willReturn(true);

        // Should return null for non-existent path after corruption cleanup
        $result = $this->subresourceIntegrityRepository->getByPath('js/jquery.js');
        $this->assertNull($result);
    }

    /**
     * Test that getByPath returns null when storage data is corrupted and safeRemove fails
     *
     * @return void
     */
    public function testGetByPathWithCorruptedDataAndFailedRemove(): void
    {
        $corruptedData = 'invalid-json-data';

        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn($corruptedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($corruptedData)
            ->willThrowException(new \InvalidArgumentException('Invalid data'));

        // safeRemove should be called but fail with an exception
        $this->storage->expects($this->once())
            ->method('remove')
            ->with($this->context)
            ->willThrowException(new \RuntimeException('Cannot remove file'));

        // Should still return null and not propagate the exception
        $result = $this->subresourceIntegrityRepository->getByPath('js/jquery.js');
        $this->assertNull($result);
    }

    /**
     * Test that getAll returns empty array when storage data is corrupted and safeRemove succeeds
     *
     * @return void
     */
    public function testGetAllWithCorruptedDataAndSuccessfulRemove(): void
    {
        $corruptedData = 'corrupted-serialized-data';

        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn($corruptedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($corruptedData)
            ->willThrowException(new \InvalidArgumentException('Unserialize failed'));

        // safeRemove should be called and succeed
        $this->storage->expects($this->once())
            ->method('remove')
            ->with($this->context)
            ->willReturn(true);

        // Should return empty array after corruption cleanup
        $result = $this->subresourceIntegrityRepository->getAll();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that multiple calls to getByPath only trigger safeRemove once due to data caching
     *
     * @return void
     */
    public function testGetByPathWithCorruptedDataCachesResult(): void
    {
        $corruptedData = 'invalid-data';

        // Storage load should only be called once
        $this->storage->expects($this->once())
            ->method('load')
            ->with($this->context)
            ->willReturn($corruptedData);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($corruptedData)
            ->willThrowException(new \InvalidArgumentException('Invalid data'));

        // safeRemove should only be called once
        $this->storage->expects($this->once())
            ->method('remove')
            ->with($this->context)
            ->willReturn(true);

        // First call
        $result1 = $this->subresourceIntegrityRepository->getByPath('js/jquery.js');
        $this->assertNull($result1);

        // Second call should use cached empty data, not call storage again
        $result2 = $this->subresourceIntegrityRepository->getByPath('js/test.js');
        $this->assertNull($result2);
    }
}
