<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEav
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\Entity;

class EavAbstractTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /**
     * Entity type id
     */
    const ENTITY_TYPE_ID = 1;

    /**
     * Abstract import entity eav model
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEav
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_string;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\ImportExport\Model\Export\Factory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    protected function setUp()
    {
        parent::setUp();

        $this->_string = new \Magento\Framework\Stdlib\StringUtils();
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_importFactory = $this->createMock(\Magento\ImportExport\Model\ImportFactory::class);
        $this->_resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_resourceHelper = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Helper::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->_collectionFactory = $this->createMock(\Magento\ImportExport\Model\Export\Factory::class);
        $this->_eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);

        $this->_model = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\Entity\AbstractEav::class,
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

    protected function tearDown()
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
