<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel;

class AdminSessionInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\Security\Model\AdminSessionInfo');
    }

    protected function tearDown()
    {
        $this->objectManager = null;
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
    protected function saveTestData()
    {
        foreach ($this->getTestData() as $key => $value) {
            $this->model->setData($key, $value);
        }
        $this->model->save();
        return $this->model->getId();
    }

    /**
     * Check that model is saving data to database
     *
     * @magentoDbIsolation enabled
     */
    public function testIsModelSavingDataToDatabase()
    {
        $modelId = $this->saveTestData();
        $newModel = $this->model->load($modelId);
        $testData = $this->getTestData();
        $newModelData = [];
        foreach (array_keys($testData) as $key) {
            $newModelData[$key] = $newModel->getData($key);
        }
        $this->assertEquals($testData, $newModelData);
    }

    /**
     * Test for deleteSessionsOlderThen() method
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testDeleteSessionsOlderThen()
    {
        $session = $this->objectManager->create('Magento\Security\Model\AdminSessionInfo');
        /** @var $session \Magento\Security\Model\AdminSessionInfo */
        $session->getResource()->deleteSessionsOlderThen(strtotime('2016-01-20 12:00:00'));
        $collection = $session->getResourceCollection()
            ->addFieldToFilter('main_table.updated_at', ['lt' => '2016-01-20 12:00:00'])
            ->load();
        $count = $collection->count();
        $this->assertEquals(0, $count);
    }

    /**
     * Test for updateStatusByUserId() method
     *
     * @magentoDataFixture Magento/Security/_files/adminsession.php
     */
    public function testUpdateStatusByUserId()
    {
        $session = $this->objectManager->create('Magento\Security\Model\AdminSessionInfo');
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
