<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * Unit Test for Class @see Magento\Csp\Model\SubresourceIntegrityRepository
 *
 */
class SubresourceIntegrityRepositoryTest extends TestCase
{

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

        $this->subresourceIntegrityRepository = new SubresourceIntegrityRepository(
            $this->cacheMock,
            $this->serializerMock,
            $this->integrityFactoryMock
        );
    }

    /** Test save repository
     *
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
        $this->cacheMock->expects($this->once())->method('load')->willReturn(false);
        $this->serializerMock->expects($this->once())->method('serialize')->with($expected)->willReturn($serialized);
        $this->cacheMock->expects($this->once())->method('save')->willReturn(true);
        $this->assertTrue($this->subresourceIntegrityRepository->save($data));
    }

    /** Test that cache saves in bunch
     *
     *
     * @return void
     */
    public function testSaveBunch(): void
    {
        $bunch1 = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'path' => 'js/jquery.js'
            ]
        );

        $bunch2 = new SubresourceIntegrity(
            [
                'hash' => 'testhash2',
                'path' => 'js/test.js'
            ]
        );

        $bunches = [$bunch1, $bunch2];

        $expected = [];

        foreach ($bunches as $bunch) {
            $expected[$bunch->getPath()] = $bunch->getHash();
        }
        $serializedBunch = json_encode($expected);
        $this->cacheMock->expects($this->once())->method('load')->willReturn(false);
        $this->serializerMock->expects($this->once())->method('serialize')
            ->with($expected)->willReturn($serializedBunch);
        $this->cacheMock->expects($this->once())->method('save')->willReturn(true);
        $this->assertTrue($this->subresourceIntegrityRepository->saveBunch($bunches));
    }
}
