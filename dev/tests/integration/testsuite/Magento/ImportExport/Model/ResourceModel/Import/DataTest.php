<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import;

use Magento\Backend\Model\Auth;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Zend_Db_Expr;

/**
 * Test Import Data resource model
 *
 * @magentoDataFixture Magento/ImportExport/_files/import_data.php
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $_model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            Data::class
        );
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is unique
     */
    public function testGetUniqueColumnData()
    {
        /** @var $objectManager ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            Registry::class
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getUniqueColumnData('entity'));
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is NOT unique
     *
     */
    public function testGetUniqueColumnDataException()
    {
        $this->expectException(LocalizedException::class);

        $this->_model->getUniqueColumnData('data');
    }

    /**
     * Test successful getBehavior()
     */
    public function testGetBehavior()
    {
        /** @var $objectManager ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            Registry::class
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['behavior'], $this->_model->getBehavior());
    }

    /**
     * Test successful getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        /** @var $objectManager ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get(
            Registry::class
        )->registry(
            '_fixture/Magento_ImportExport_Import_Data'
        );

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getEntityTypeCode());
    }

    /**
     * Test that users import data are isolated from each other
     */
    public function testUsersImportDataShouldBeIsolated()
    {
        $count = $this->_model->getConnection()->fetchOne(
            $this->_model->getConnection()->select()->from($this->_model->getMainTable(), new Zend_Db_Expr('count(*)'))
        );
        $auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(Auth::class);
        $auth->login(Bootstrap::ADMIN_NAME, Bootstrap::ADMIN_PASSWORD);
        $bunches = [
            0 => [
                'entity' => 'customer',
                'behavior' => 'delete',
                'data' => [
                    [
                        'email' => 'mike.miller.101@magento.com',
                        '_website' => 'base',
                    ],
                    [
                        'email' => 'john.doe.102@magento.com',
                        '_website' => 'base',
                    ]
                ]
            ],
            1 => [
                'entity' => 'customer',
                'behavior' => 'delete',
                'data' => [
                    [
                        'email' => 'jack.simon.103@magento.com',
                        '_website' => 'base',
                    ],
                ],
            ],
        ];
        $expectedData = [];
        foreach ($bunches as $bunch) {
            $this->_model->saveBunch($bunch['entity'], $bunch['behavior'], $bunch['data']);
            $expectedData[] = $bunch['data'];
        }
        $expectedData = array_merge(...$expectedData);
        $actualData = [];
        while ($data = $this->_model->getNextBunch()) {
            $actualData[] = $data;
        }
        $actualData = array_merge(...$actualData);
        $this->assertEquals($expectedData, $actualData);
        $this->_model->cleanBunches();
        $actualData = [];
        while ($data = $this->_model->getNextBunch()) {
            $actualData[] = $data;
        }
        $this->assertEmpty($actualData);
        $newCount = $this->_model->getConnection()->fetchOne(
            $this->_model->getConnection()->select()->from($this->_model->getMainTable(), new Zend_Db_Expr('count(*)'))
        );
        $this->assertEquals($count, $newCount);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->_model->getConnection()->delete($this->_model->getMainTable());
        parent::tearDown();
    }
}
