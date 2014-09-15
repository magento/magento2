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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEav
 */
namespace Magento\ImportExport\Model\Import\Entity;

class EavAbstractTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreDataMock;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_string;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\ImportExport\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\StoreManagerInterface
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
        $this->_coreDataMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_string = new \Magento\Framework\Stdlib\String();
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_importFactory = $this->getMock(
            'Magento\ImportExport\Model\ImportFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->_resourceHelper = $this->getMock(
            'Magento\ImportExport\Model\Resource\Helper',
            array(),
            array(),
            '',
            false
        );
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\ImportExport\Model\Export\Factory',
            array(),
            array(),
            '',
            false
        );
        $this->_eavConfig = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Import\Entity\AbstractEav',
            array(
                $this->_coreDataMock,
                $this->_string,
                $scopeConfig,
                $this->_importFactory,
                $this->_resourceHelper,
                $this->_resource,
                $this->_storeManager,
                $this->_collectionFactory,
                $this->_eavConfig,
                $this->_getModelDependencies()
            )
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
        $data = array(
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
            'entity_type_id' => self::ENTITY_TYPE_ID
        );

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
