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
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Resource\Attribute
     */
    protected $subjectMock;

    /**
     * @var Attribute
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Resource\Attribute', [], [], '', false);
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->model = new Attribute($this->indexerMock);
    }

    /**
     * @param bool $isObjectNew
     * @param bool $isSearchableChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($isObjectNew, $isSearchableChanged, $invalidateCounter)
    {
        $attributeMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('is_searchable')
            ->will($this->returnValue($isSearchableChanged));
        $attributeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Catalog\Model\Resource\Eav\Attribute $object) use ($attributeMock) {
            $this->assertEquals($object, $attributeMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $attributeMock)
        );
    }

    /**
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }

    /**
     * @param bool $isObjectNew
     * @param bool $isSearchable
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundDeleteDataProvider
     */
    public function testAroundDelete($isObjectNew, $isSearchable, $invalidateCounter)
    {
        $attributeMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getIsSearchable', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->any())->method('getIsSearchable')->will($this->returnValue($isSearchable));
        $attributeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Catalog\Model\Resource\Eav\Attribute $object) use ($attributeMock) {
            $this->assertEquals($object, $attributeMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('getId')->will($this->returnValue(1));
        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $closureMock, $attributeMock)
        );
    }

    /**
     * @return array
     */
    public function aroundDeleteDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }
}
