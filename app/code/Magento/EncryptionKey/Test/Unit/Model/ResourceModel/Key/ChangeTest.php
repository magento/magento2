<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Test\Unit\Model\ResourceModel\Key;

/**
 * Test Class For Magento\EncryptionKey\Model\ResourceModel\Key\Change
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $encryptMock;

    /** @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject */
    protected $filesystemMock;

    /** @var \Magento\Config\Model\Config\Structure|\PHPUnit\Framework\MockObject\MockObject */
    protected $structureMock;

    /** @var \Magento\Framework\App\DeploymentConfig\Writer|\PHPUnit\Framework\MockObject\MockObject */
    protected $writerMock;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $adapterMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject */
    protected $resourceMock;

    /** @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject */
    protected $selectMock;

    /** @var \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface */
    protected $transactionMock;

    /** @var |\PHPUnit\Framework\MockObject\MockObject */
    protected $objRelationMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit\Framework\MockObject\MockObject */
    protected $randomMock;

    /** @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change */
    protected $model;

    protected function setUp(): void
    {
        $this->encryptMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNewKey', 'exportKeys'])
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->structureMock = $this->getMockBuilder(\Magento\Config\Model\Config\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->writerMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig\Writer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'where', 'update'])
            ->getMock();
        $translationClassName = \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class;
        $this->transactionMock = $this->getMockBuilder($translationClassName)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $relationClassName = \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class;
        $this->objRelationMock = $this->getMockBuilder($relationClassName)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->randomMock = $this->createMock(\Magento\Framework\Math\Random::class);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $helper->getObject(
            \Magento\EncryptionKey\Model\ResourceModel\Key\Change::class,
            [
                'filesystem' => $this->filesystemMock,
                'structure' => $this->structureMock,
                'encryptor' => $this->encryptMock,
                'writer' => $this->writerMock,
                'adapterInterface' => $this->adapterMock,
                'resource' => $this->resourceMock,
                'transactionManager' => $this->transactionMock,
                'relationProcessor' => $this->objRelationMock,
                'random' => $this->randomMock
            ]
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
        $this->randomMock->expects($this->never())->method('getRandomString');
        $key = 'key';
        $this->assertEquals($key, $this->model->changeEncryptionKey($key));
    }

    public function testChangeEncryptionKeyAutogenerate()
    {
        $this->setUpChangeEncryptionKey();
        $this->randomMock->expects($this->once())->method('getRandomString')->willReturn('abc');
        $this->assertEquals(md5('abc'), $this->model->changeEncryptionKey());
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
