<?php
namespace Magento\Security\Model;

/**
 * Class PasswordResetRequestEventTest
 * @package Magento\Security\Model
 */
class PasswordResetRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_model;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create('Magento\Security\Model\PasswordResetRequestEvent');
        $this->_resourceModel = $this->_model->getResource();
    }

    protected function tearDown()
    {
        $this->_objectManager = null;
        $this->_resourceModel = null;
        parent::tearDown();
    }

    /**
     * Test data
     * @return array
     */
    public function getTestData()
    {
        return [
            'request_type'      => PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
            'account_reference' => 'test27.dev@gmail.com',
            'created_at'        => '2016-01-20 13:00:13',
            'ip'                => '3232249856'
        ];
    }

    /**
     * Saving test data to database
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
     * Checking that test data is saving to database
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
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testDeleteRecordsOlderThen()
    {
        /** @var \Magento\Security\Model\PasswordResetRequestEvent $passwordResetRequestEvent */
        $countBefore = $this->_model->getCollection()->count();
        $this->_resourceModel->deleteRecordsOlderThen(strtotime('2016-01-20 12:00:00'));
        $countAfter = $this->_model->getCollection()->count();
        $this->assertLessThan($countBefore, $countAfter);
    }

}