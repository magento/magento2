<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Test\Unit\Model\ResourceModel\Key;

use Magento\Config\Model\Config\Structure;
use Magento\EncryptionKey\Model\ResourceModel\Key\Change;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Class For Magento\EncryptionKey\Model\ResourceModel\Key\Change
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeTest extends TestCase
{
    use MockCreationTrait;

    /** @var EncryptorInterface|MockObject */
    protected $encryptMock;

    /** @var Filesystem|MockObject */
    protected $filesystemMock;

    /** @var Structure|MockObject */
    protected $structureMock;

    /** @var Writer|MockObject */
    protected $writerMock;

    /** @var AdapterInterface|MockObject */
    protected $adapterMock;

    /** @var ResourceConnection|MockObject */
    protected $resourceMock;

    /** @var Select|MockObject */
    protected $selectMock;

    /** @var TransactionManagerInterface */
    protected $transactionMock;

    /** @var MockObject */
    protected $objRelationMock;

    /** @var Random|MockObject */
    protected $randomMock;

    /** @var Change */
    protected $model;

    protected function setUp(): void
    {
        $this->encryptMock = $this->createPartialMockWithReflection(
            EncryptorInterface::class,
            [
                'getHash',
                'hash',
                'validateHash',
                'isValidHash',
                'validateHashVersion',
                'encrypt',
                'decrypt',
                'validateKey',
                'setNewKey',
                'exportKeys'
            ]
        );
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->structureMock = $this->createMock(Structure::class);
        $this->writerMock = $this->createMock(Writer::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->selectMock = $this->createPartialMockWithReflection(
            Select::class,
            ['update', 'from', 'where']
        );
        $this->transactionMock = $this->createMock(TransactionManagerInterface::class);
        $this->objRelationMock = $this->createMock(ObjectRelationProcessor::class);
        $this->randomMock = $this->createMock(Random::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getResources']
        );
        $contextMock->method('getResources')->willReturn($this->resourceMock);
        
        $this->model = new Change(
            $contextMock,
            $this->filesystemMock,
            $this->structureMock,
            $this->encryptMock,
            $this->writerMock,
            $this->randomMock
        );
    }

    private function setUpChangeEncryptionKey()
    {
        $paths = ['path1', 'path2'];
        $table = ['item1', 'item2'];
        $values = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $this->writerMock->expects($this->once())->method('checkIfWritable')->willReturn(true);
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->once())->method('beginTransaction');
        $this->structureMock->expects($this->once())->method('getFieldPathsByAttribute')->willReturn($paths);
        $this->resourceMock->expects($this->atLeastOnce())->method('getTableName')->willReturn($table);
        $this->adapterMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->adapterMock->expects($this->any())->method('fetchPairs')->willReturn($values);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('update')->willReturnSelf();
        $this->writerMock->expects($this->once())->method('saveConfig');
        $this->adapterMock->expects($this->once())->method('getTransactionLevel')->willReturn(1);
    }

    public function testChangeEncryptionKey()
    {
        $this->setUpChangeEncryptionKey();
        $this->randomMock->expects($this->never())->method('getRandomBytes');
        $key = 'key';
        $this->assertEquals($key, $this->model->changeEncryptionKey($key));
    }

    public function testChangeEncryptionKeyAutogenerate()
    {
        $this->setUpChangeEncryptionKey();
        $this->randomMock->expects($this->once())->method('getRandomBytes')->willReturn('abc');
        $this->assertEquals(
            ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX . 'abc',
            $this->model->changeEncryptionKey()
        );
    }

    public function testChangeEncryptionKeyThrowsException()
    {
        $key = 'key';
        $this->writerMock->expects($this->once())->method('checkIfWritable')->willReturn(false);

        try {
            $this->model->changeEncryptionKey($key);
        } catch (\Exception $e) {
            return;
        }

        $this->fail('An expected exception was not signaled.');
    }
}
