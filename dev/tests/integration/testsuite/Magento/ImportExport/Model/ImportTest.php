<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * @magentoDataFixture Magento/ImportExport/_files/import_data.php
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Model object which is used for tests
     *
     * @var Import
     */
    protected $_model;

    /**
     * @var \Magento\ImportExport\Model\Import\Config
     */
    protected $_importConfig;

    /**
     * Expected entity behaviors
     *
     * @var array
     */
    protected $_entityBehaviors = [
        'catalog_product' => [
            'token' => \Magento\ImportExport\Model\Source\Import\Behavior\Basic::class,
            'code' => 'basic_behavior',
            'notes' => [
                \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE => "Note: Product IDs will be regenerated."
            ],
        ],
        'customer_composite' => [
            'token' => \Magento\ImportExport\Model\Source\Import\Behavior\Basic::class,
            'code' => 'basic_behavior',
            'notes' => [],
        ],
        'customer' => [
            'token' => \Magento\ImportExport\Model\Source\Import\Behavior\Custom::class,
            'code' => 'custom_behavior',
            'notes' => [],
        ],
        'customer_address' => [
            'token' => \Magento\ImportExport\Model\Source\Import\Behavior\Custom::class,
            'code' => 'custom_behavior',
            'notes' => [],
        ],
    ];

    /**
     * Expected unique behaviors
     *
     * @var array
     */
    protected $_uniqueBehaviors = [
        'basic_behavior' => \Magento\ImportExport\Model\Source\Import\Behavior\Basic::class,
        'custom_behavior' => \Magento\ImportExport\Model\Source\Import\Behavior\Custom::class,
    ];

    protected function setUp()
    {
        $this->_importConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import\Config::class
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Import::class,
            ['importConfig' => $this->_importConfig]
        );
    }

    /**
     * @covers \Magento\ImportExport\Model\Import::_getEntityAdapter
     */
    public function testImportSource()
    {
        /** @var $customersCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $customersCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        );

        $existCustomersCount = count($customersCollection->load());

        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model->setData(
            Import::FIELD_NAME_VALIDATION_STRATEGY,
            ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
        );
        $this->_model->importSource();

        $customers = $customersCollection->getItems();

        $addedCustomers = count($customers) - $existCustomersCount;

        $this->assertGreaterThan($existCustomersCount, $addedCustomers);
    }

    public function testValidateSource()
    {
        $validationStrategy = ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;

        $this->_model->setEntity('catalog_product');
        $this->_model->setData(\Magento\ImportExport\Model\Import::FIELD_NAME_VALIDATION_STRATEGY, $validationStrategy);
        $this->_model->setData(\Magento\ImportExport\Model\Import::FIELD_NAME_ALLOWED_ERROR_COUNT, 0);

        /** @var \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\AbstractSource::class,
            [['sku', 'name']]
        );
        $source->expects($this->any())->method('_getNextRow')->will($this->returnValue(false));
        $this->assertTrue($this->_model->validateSource($source));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Entity is unknown
     */
    public function testValidateSourceException()
    {
        $source = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\AbstractSource::class,
            [],
            '',
            false
        );
        $this->_model->validateSource($source);
    }

    public function testGetEntity()
    {
        $entityName = 'entity_name';
        $this->_model->setEntity($entityName);
        $this->assertSame($entityName, $this->_model->getEntity());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Entity is unknown
     */
    public function testGetEntityEntityIsNotSet()
    {
        $this->_model->getEntity();
    }

    /**
     * Test getEntityBehaviors with all required data
     * Can't check array on equality because this test should be useful for CE
     *
     * @covers \Magento\ImportExport\Model\Import::getEntityBehaviors
     */
    public function testGetEntityBehaviors()
    {
        $importModel = $this->_model;
        $actualBehaviors = $importModel->getEntityBehaviors();

        foreach ($this->_entityBehaviors as $entityKey => $behaviorData) {
            $this->assertArrayHasKey($entityKey, $actualBehaviors);
            $this->assertEquals($behaviorData, $actualBehaviors[$entityKey]);
        }
    }

    /**
     * Test getEntityBehaviors with not existing behavior class
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The behavior token for customer is invalid.
     */
    public function testGetEntityBehaviorsWithUnknownBehavior()
    {
        $this->_importConfig->merge(
            ['entities' => ['customer' => ['behaviorModel' => 'Unknown_Behavior_Class']]]
        );
        $importModel = $this->_model;
        $actualBehaviors = $importModel->getEntityBehaviors();
        $this->assertArrayNotHasKey('customer', $actualBehaviors);
    }

    /**
     * Test getUniqueEntityBehaviors with all required data
     * Can't check array on equality because this test should be useful for CE
     *
     * @covers \Magento\ImportExport\Model\Import::getUniqueEntityBehaviors
     */
    public function testGetUniqueEntityBehaviors()
    {
        $importModel = $this->_model;
        $actualBehaviors = $importModel->getUniqueEntityBehaviors();

        foreach ($this->_uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $this->assertArrayHasKey($behaviorCode, $actualBehaviors);
            $this->assertEquals($behaviorClass, $actualBehaviors[$behaviorCode]);
        }
    }
}
