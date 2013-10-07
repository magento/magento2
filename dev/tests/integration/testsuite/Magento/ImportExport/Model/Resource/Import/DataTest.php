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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     <package-name>
 * @subpackage  <subpackage-name>
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ImportExport\Model\Resource\Import;

/**
 * Test Import Data resource model
 *
 * @magentoDataFixture Magento/ImportExport/_files/import_data.php
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Resource\Import\Data
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\ImportExport\Model\Resource\Import\Data');
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is unique
     */
    public function testGetUniqueColumnData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get('Magento\Core\Model\Registry')
            ->registry('_fixture/Magento_ImportExport_Import_Data');

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getUniqueColumnData('entity'));
    }

    /**
     * Test getUniqueColumnData() in case when in data stored in requested column is NOT unique
     *
     * @expectedException \Magento\Core\Exception
     */
    public function testGetUniqueColumnDataException()
    {
        $this->_model->getUniqueColumnData('data');
    }

    /**
     * Test successful getBehavior()
     */
    public function testGetBehavior()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get('Magento\Core\Model\Registry')
            ->registry('_fixture/Magento_ImportExport_Import_Data');

        $this->assertEquals($expectedBunches[0]['behavior'], $this->_model->getBehavior());
    }

    /**
     * Test successful getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $expectedBunches = $objectManager->get('Magento\Core\Model\Registry')
            ->registry('_fixture/Magento_ImportExport_Import_Data');

        $this->assertEquals($expectedBunches[0]['entity'], $this->_model->getEntityTypeCode());
    }
}
