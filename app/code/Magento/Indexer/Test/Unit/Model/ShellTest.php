<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Shell
     */
    protected $model;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexersFactoryMock;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerFactoryMock;

    /**
     * Set up test
     */
    protected function setUp()
    {
        $entryPoint = '';

        $this->filesystemMock = $this->getMock(
            '\Magento\Framework\Filesystem',
            ['getDirectoryRead'],
            [],
            '',
            false
        );
        $this->indexerFactoryMock = $this->getMock(
            'Magento\Indexer\Model\IndexerFactory',
            ['create', 'load', 'reindexAll', 'getTitle'],
            [],
            '',
            false
        );
        $this->indexersFactoryMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\CollectionFactory',
            ['create', 'getItems'],
            [],
            '',
            false
        );
        $readInterfaceMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->filesystemMock->expects($this->once())->method('getDirectoryRead')->will(
            $this->returnValue($readInterfaceMock)
        );

        $this->model = new \Magento\Indexer\Model\Shell(
            $this->filesystemMock,
            $entryPoint,
            $this->indexersFactoryMock,
            $this->indexerFactoryMock
        );
    }

    /**
     * @param string $args
     * @param string $status
     * @dataProvider runDataProvider
     */
    public function testRun($args, $status = null)
    {
        $withItems = [
            'info',
            'status',
            'mode',
            '--mode-realtime',
            '--mode-schedule',
            'reindex',
            'reindexall'
        ];
        $this->model->setRawArgs(['testme.php', '--', $args]);
        
        $indexerMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer',
            ['getStatus', 'isScheduled', 'reindexAll', 'turnViewOff', 'turnViewOn'],
            [],
            '',
            false
        );

        if (in_array($args, $withItems)) {
            if ($args == 'status') {
                $indexerMock->expects($this->any())->method('getStatus')->will(
                    $this->returnValue($status)
                );
            }
            $this->indexersFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
            $this->indexersFactoryMock->expects($this->once())->method('getItems')->will(
                $this->returnValue([$indexerMock])
            );
        }
        if ($args == '--reindex=price') {
            $this->indexerFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
            $this->indexerFactoryMock->expects($this->any())->method('load')->will(
                $this->returnValue($indexerMock)
            );
        }

        ob_start();
        $this->assertInstanceOf('\Magento\Indexer\Model\Shell', $this->model->run());
        ob_end_clean();
    }

    /**
     * @return array
     */
    public function runDataProvider()
    {
        return [
            ['h'],
            ['info'],
            ['mode'],
            ['status', \Magento\Indexer\Model\Indexer\State::STATUS_VALID],
            ['status', \Magento\Indexer\Model\Indexer\State::STATUS_INVALID],
            ['status', \Magento\Indexer\Model\Indexer\State::STATUS_WORKING],
            ['--mode-realtime'],
            ['--mode-schedule'],
            ['reindex'],
            ['reindexall'],
            ['--reindex=price'],
            ['other']
        ];
    }

    /**
     * @param string $args
     * @dataProvider runExceptionDataProvider
     */
    public function testRunException($args)
    {
        $indexerMock = $this->getMock(
            '\Magento\Indexer\Model\Indexer',
            ['reindexAll', 'turnViewOff', 'turnViewOn'],
            [],
            '',
            false
        );

        $this->model->setRawArgs(['testme.php', '--', $args]);

        if ($args == 'reindex') {
            $indexerMock->expects($this->any())->method('reindexAll')->will(
                $this->throwException(new \Exception())
            );
        }
        if ($args == '--mode-realtime') {
            $indexerMock->expects($this->any())->method('turnViewOff')->will(
                $this->throwException(new \Exception())
            );
        }
        if ($args == 'reindexall') {
            $indexerMock->expects($this->any())->method('reindexAll')->will(
                $this->throwException(
                    new \Magento\Framework\Exception\LocalizedException(
                        __('Something went wrong during reindexing all.')
                    )
                )
            );
        }
        if ($args == '--mode-schedule') {
            $indexerMock->expects($this->any())->method('turnViewOn')->will(
                $this->throwException(
                    new \Magento\Framework\Exception\LocalizedException(
                        __('Something went wrong during turning view on.')
                    )
                )
            );
        }
        if ($args == '--reindex=price') {
            $this->indexerFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
            $this->indexerFactoryMock->expects($this->any())->method('load')->will(
                $this->throwException(new \Exception())
            );
        } else {
            $this->indexersFactoryMock->expects($this->once())->method('create')->will($this->returnSelf());
            $this->indexersFactoryMock->expects($this->once())->method('getItems')->will(
                $this->returnValue([$indexerMock])
            );
        }

        ob_start();
        $this->assertInstanceOf('\Magento\Indexer\Model\Shell', $this->model->run());
        ob_end_clean();

        $this->assertEquals(true, $this->model->hasErrors());
    }

    /**
     * @return array
     */
    public function runExceptionDataProvider()
    {
        return [
            ['reindex'],
            ['reindexall'],
            ['--mode-realtime'],
            ['--mode-schedule'],
            ['--reindex=price']
        ];
    }
}
