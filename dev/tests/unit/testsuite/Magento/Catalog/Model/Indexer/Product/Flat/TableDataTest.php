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
namespace Magento\Catalog\Model\Indexer\Product\Flat;

class TableDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->_resourceMock = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
    }

    /**
     * @param string $flatTable
     * @param bool $isFlatTableExists
     * @param string $flatDropName
     * @param string $temporaryFlatTableName
     * @param array $expectedRenameTablesArgument
     * @dataProvider moveDataProvider
     */
    public function testMove(
        $flatTable,
        $isFlatTableExists,
        $flatDropName,
        $temporaryFlatTableName,
        $expectedRenameTablesArgument
    ) {
        $this->_connectionMock->expects($this->exactly(2))->method('dropTable')->with($flatDropName);
        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $flatTable
        )->will(
            $this->returnValue($isFlatTableExists)
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'renameTablesBatch'
        )->with(
            $expectedRenameTablesArgument
        );

        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->with(
            'write'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Flat\TableData',
            array('resource' => $this->_resourceMock)
        );

        $model->move($flatTable, $flatDropName, $temporaryFlatTableName);
    }

    /**
     * @return array
     */
    public function moveDataProvider()
    {
        return array(
            array(
                'flat_table',
                true,
                'flat_table_to_drop',
                'flat_tmp',
                array(
                    array('oldName' => 'flat_table', 'newName' => 'flat_table_to_drop'),
                    array('oldName' => 'flat_tmp', 'newName' => 'flat_table')
                )
            ),
            array(
                'flat_table',
                false,
                'flat_table_to_drop',
                'flat_tmp',
                array(array('oldName' => 'flat_tmp', 'newName' => 'flat_table'))
            )
        );
    }
}
