<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEav
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\Entity;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import\Entity\AbstractEav;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class EavAbstractTest extends AbstractImportTestCase
{
    private const ENTITY_TYPE_ID = 1;

    /**
     * Abstract import entity eav model
     *
     * @var AbstractEav
     */
    protected $_model;

    /**
     * @var StringUtils|MockObject
     */
    protected $_string;

    /**
     * @var ImportFactory
     */
    protected $_importFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Factory
     */
    protected $_collectionFactory;

    /**
     * @var Config
     */
    protected $_eavConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->_string = new StringUtils();
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->_importFactory = $this->createMock(ImportFactory::class);
        $this->_resource = $this->createMock(ResourceConnection::class);
        $this->_resourceHelper = $this->createMock(Helper::class);
        $this->_storeManager = $this->createMock(StoreManager::class);
        $this->_collectionFactory = $this->createMock(Factory::class);
        $this->_eavConfig = $this->createMock(Config::class);

        $this->_model = $this->getMockForAbstractClass(
            AbstractEav::class,
            [
                $this->_string,
                $scopeConfig,
                $this->_importFactory,
                $this->_resourceHelper,
                $this->_resource,
                $this->getErrorAggregatorObject(),
                $this->_storeManager,
                $this->_collectionFactory,
                $this->_eavConfig,
                $this->_getModelDependencies()
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $data = [
            'data_source_model' => 'not_used',
            'connection' => 'not_used',
            'json_helper' => 'not_used',
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'collection_by_pages_iterator' => 'not_used',
            'website_manager' => 'not_used',
            'store_manager' => 'not_used',
            'attribute_collection' => 'not_used',
            'entity_type_id' => self::ENTITY_TYPE_ID,
        ];

        return $data;
    }

    /**
     * Test entity type id getter
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEav::getEntityTypeId
     */
    public function testGetEntityTypeId()
    {
        $this->assertEquals(self::ENTITY_TYPE_ID, $this->_model->getEntityTypeId());
    }
}
