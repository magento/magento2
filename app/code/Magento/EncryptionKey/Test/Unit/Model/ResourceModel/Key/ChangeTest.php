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
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection as StateCollection;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;
use Magento\Indexer\Model\Indexer\State;

/**
 * Test Class For Magento\EncryptionKey\Model\ResourceModel\Key\Change
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeTest extends TestCase
{
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

    /** @var ConfigInterface|MockObject */
    protected $indexerConfigMock;

    /** @var EncoderInterface|MockObject */
    protected $encoderMock;

    /** @var CollectionFactory|MockObject */
    protected $indexerStateCollectionMock;

    protected function setUp(): void
    {
        $this->encryptMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setNewKey', 'exportKeys'])
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->addMethods(['update'])
            ->onlyMethods(['from', 'where'])
            ->getMock();
        $translationClassName = TransactionManagerInterface::class;
        $this->transactionMock = $this->getMockBuilder($translationClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $relationClassName = ObjectRelationProcessor::class;
        $this->objRelationMock = $this->getMockBuilder($relationClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $this->randomMock = $this->createMock(Random::class);
        $this->indexerConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->encoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->indexerStateCollectionMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->addMethods(['getItems'])
            ->getMock();

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            Change::class,
            [
                'filesystem' => $this->filesystemMock,
                'structure' => $this->structureMock,
                'encryptor' => $this->encryptMock,
                'writer' => $this->writerMock,
                'adapterInterface' => $this->adapterMock,
                'resource' => $this->resourceMock,
                'transactionManager' => $this->transactionMock,
                'relationProcessor' => $this->objRelationMock,
                'random' => $this->randomMock,
                'indexerConfig' => $this->indexerConfigMock,
                'encoder' => $this->encoderMock,
                'indexerStateCollection' => $this->indexerStateCollectionMock
            ]
        );
    }

    /**
     * @param array $indexersData
     * @param array $states
     */
    private function setUpChangeEncryptionKey(array $indexersData, array $states)
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

        $indexerStateCollection = $this->getMockBuilder(StateCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerStateCollectionMock->expects($this->once())
            ->method('create')->willReturn($indexerStateCollection);

        $finalStates = [];

        foreach ($states as $key => $state) {
            if (is_callable($state)) {
                $finalStates[$key] = $state($this);
            }
        }

        $indexerStateCollection->method('getItems')
            ->willReturn($finalStates);

        $this->indexerConfigMock->expects($this->any())->method('getIndexers')->willReturn($indexersData);
    }

    /**
     * @param array $indexersData
     * @param array $states
     * @dataProvider loadDataDataProvider
     */
    public function testChangeEncryptionKey(array $indexersData, array $states)
    {

        $this->setUpChangeEncryptionKey($indexersData, $states);
        $this->randomMock->expects($this->never())->method('getRandomBytes');
        $key = 'key';
        $this->assertEquals($key, $this->model->changeEncryptionKey($key));
    }

    /**
     * @param array $indexersData
     * @param array $states
     * @dataProvider loadDataDataProvider
     */
    public function testChangeEncryptionKeyAutogenerate(array $indexersData, array $states)
    {
        $this->setUpChangeEncryptionKey($indexersData, $states);
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

    /**
     * @param array $data
     * @return MockObject|State
     */
    private function getStateMock(array $data = [])
    {
        /** @var MockObject|State $state */
        $state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        if (isset($data['indexer_id'])) {
            $state->method('getIndexerId')
                ->willReturn($data['indexer_id']);
        }

        return $state;
    }

    /**
     * @return array
     */
    public static function loadDataDataProvider()
    {
        return [
            [
                'indexersData' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                    ],
                ],
                'states' => [
                    'indexer_2' => static fn (self $testCase) => $testCase->getStateMock(['indexer_id' => 'indexer_2']),
                    'indexer_3' => static fn (self $testCase) => $testCase->getStateMock(['indexer_id' => 'indexer_3']),
                ],
            ]
        ];
    }
}
