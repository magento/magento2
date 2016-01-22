<?php

namespace Magento\Security\Model;

/**
 * Class AdminSessionInfoTest
 * @package Magento\Security\Model
 */
class AdminSessionInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
    }

    protected function tearDown()
    {
        $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
        /** @var $session \Magento\Security\Model\AdminSessionInfo */
        $session->getResource()->getConnection()->delete(
            $session->getResource()->getMainTable()
        );
        $this->_objectManager = null;
        parent::tearDown();
    }

    /**
     * Test data for saving
     * @return array
     */
    public function getTestData()
    {
        return [
            'session_id'    => '569e273d752e9',
            'user_id'       => 1,
            'status'        => 1,
            'created_at'    => '2016-01-21 15:00:00',
            'updated_at'    => '2016-01-21 18:00:00'
        ];
    }

    /**
     * @return mixed
     */
    private function _saveTestData()
    {
        foreach ($this->getTestData() as $key => $value) {
            $this->_model->setData($key, $value);
        }
        $this->_model->save();
        return $this->_model->getId();
    }

    /**
     * checking that model is saving data to database
     */
    public function testIsModelSavingDataToDatabase()
    {
        $modelId = $this->_saveTestData();
        $newModel = $this->_model->load($modelId);
        $testData = $this->getTestData();
        $newModelData = array();
        foreach ($testData as $key => $value)
        {
            $newModelData[$key] = $newModel->getData($key);
        }
        $this->assertEquals($testData, $newModelData);
    }

    /**
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testDeleteSessionsOlderThen()
    {
        $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
        /** @var $session \Magento\Security\Model\AdminSessionInfo */
        $session->getResource()->deleteSessionsOlderThen(strtotime('2016-01-20 12:00:00'));
        $collection = $session->getResourceCollection()
            ->addFieldToFilter('main_table.updated_at', ['lt' => '2016-01-20 12:00:00'])
            ->load();
        $count = $collection->count();
        $this->assertEquals(0, $count);
    }

    /**
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testUpdateStatusByUserId()
    {
        $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
        /** @var $session \Magento\Security\Model\AdminSessionInfo */
        $session->getResource()->updateStatusByUserId(
            \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
            1,
            [1],
            [1],
            '2016-01-19 12:00:00'
        );
        $collection = $session->getResourceCollection()
            ->addFieldToFilter('main_table.user_id', 1)
            ->addFieldToFilter('main_table.status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN)
            ->load();
        $count = $collection->count();
        $this->assertGreaterThanOrEqual(1, $count);
    }

}
