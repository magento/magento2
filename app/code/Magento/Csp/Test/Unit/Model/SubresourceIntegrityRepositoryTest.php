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
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'load'])
            ->getMockForAbstractClass();
        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'unserialize'])
            ->getMockForAbstractClass();
        $this->integrityFactoryMock = $this->getMockBuilder(SubresourceIntegrityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storage = $this->getMockBuilder(StorageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
}
