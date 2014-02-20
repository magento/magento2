<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Index
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Index\Model;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test exception message
     */
    const EXCEPTION_MESSAGE = 'Test exception message';

    /**
     * Indexer used for test
     */
    const INDEXER_CODE = 'catalog_url';

    /**
     * @var array
     */
    protected $_indexerMatchData = array(
        'new_data' => array(\Magento\Catalog\Model\Indexer\Url::EVENT_MATCH_RESULT_KEY => true)
    );

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Index\Model\Process
     */
    protected $_model;

    /**
     * @var \Magento\Index\Model\Process\File
     */
    protected $_processFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventRepositoryMock;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_eventRepositoryMock = $this->getMock(
            'Magento\Index\Model\EventRepository', array(), array(), '', false
        );

        // get existing indexer process
        $this->_model = $this->_objectManager->create(
            'Magento\Index\Model\Process', array('eventRepository' => $this->_eventRepositoryMock)
        );
        $this->_model->load(self::INDEXER_CODE, 'indexer_code');
        if ($this->_model->isObjectNew()) {
            $this->markTestIncomplete('Can\'t run test without ' . self::INDEXER_CODE . ' indexer.');
        }

        // get new process file instance for current indexer
        /** @var $lockStorage \Magento\Index\Model\Lock\Storage */
        $lockStorage = $this->_objectManager->create('Magento\Index\Model\Lock\Storage');
        $this->_processFile = $lockStorage->getFile($this->_model->getId());
    }

    /**
     * @return array
     */
    public function safeProcessEventDataProvider()
    {
        return array(
            'not_matched' => array(
                '$eventData' => array(),
            ),
            'locked' => array(
                '$eventData' => $this->_indexerMatchData,
                '$needLock'  => true,
            ),
            'matched' => array(
                '$eventData' => $this->_indexerMatchData,
            ),
        );
    }

    /**
     * @param array $eventData
     * @param bool $needLock
     *
     * @dataProvider safeProcessEventDataProvider
     */
    public function testSafeProcessEvent(array $eventData, $needLock = false)
    {
        if ($needLock) {
            $this->_processFile->processLock();
        }

        $event = $this->_objectManager->create('Magento\Index\Model\Event', array('data' => $eventData));
        $this->assertEquals($this->_model, $this->_model->safeProcessEvent($event));

        if ($needLock) {
            $this->_processFile->processUnlock();
        }

        $this->assertFalse($this->_processFile->isProcessLocked(true));
    }

    public function testSafeProcessEventException()
    {
        // prepare mock that throws exception
        /** @var $eventMock \Magento\Index\Model\Event */
        $eventMock = $this->getMock('Magento\Index\Model\Event', array('setProcess'), array(), '', false);
        $eventMock->setData($this->_indexerMatchData);
        $exceptionMessage = self::EXCEPTION_MESSAGE;
        $eventMock->expects($this->any())
            ->method('setProcess')
            ->will($this->returnCallback(
                function () use ($exceptionMessage) {
                    throw new \Exception($exceptionMessage);
                }
            ));

        // can't use @expectedException because we need to assert indexer lock status
        try {
            $this->_model->safeProcessEvent($eventMock);
        } catch (\Exception $e) {
            $this->assertEquals(self::EXCEPTION_MESSAGE, $e->getMessage());
        }

        $this->assertFalse($this->_processFile->isProcessLocked(true));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testReindexAllDoesntTriggerUnprocessedEventFetchingInManualMode()
    {
        $collection = $this->_objectManager->create('Magento\Index\Model\Resource\Event\Collection');
        $this->_model->setMode(\Magento\Index\Model\Process::MODE_REAL_TIME);
        $this->_model->setStatus(\Magento\Index\Model\Process::STATUS_PENDING);
        $this->_eventRepositoryMock->expects($this->once())->method('getUnprocessed')
            ->will($this->returnValue($collection));
        $this->_eventRepositoryMock->expects($this->never())->method('hasUnprocessed');
        $this->_model->reindexAll();
    }
}
