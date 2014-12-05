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
namespace Magento\Cms\Model\Resource;

/**
 * Class PageCriteriaMapperTest
 */
class PageCriteriaMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\ObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \Magento\Framework\DB\MapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperFactoryMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Cms\Model\Resource\PageCriteriaMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCriteria;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMock(
            'Magento\Framework\Logger',
            [],
            [],
            '',
            false
        );
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface',
            [],
            '',
            false
        );
        $this->objectFactoryMock = $this->getMock(
            'Magento\Framework\Data\ObjectFactory',
            [],
            [],
            '',
            false
        );
        $this->mapperFactoryMock = $this->getMock(
            'Magento\Framework\DB\MapperFactory',
            [],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['join', 'group', 'where'],
            [],
            '',
            false
        );

        $this->pageCriteria = $this->getMockBuilder('Magento\Cms\Model\Resource\PageCriteriaMapper')
            ->setConstructorArgs(
                [
                    'logger' => $this->loggerMock,
                    'fetchStrategy' => $this->fetchStrategyMock,
                    'objectFactory' => $this->objectFactoryMock,
                    'mapperFactory' => $this->mapperFactoryMock,
                    'select' => $this->selectMock
                ]
            )->setMethods(['init', 'getTable', 'getMappedField', 'getConditionSql'])
            ->getMock();
    }

    /**
     * Run test mapStoreFilter method
     *
     * @return void
     */
    public function testMapStoreFilter()
    {
        $reflection = new \ReflectionClass($this->pageCriteria);
        $reflectionProperty = $reflection->getProperty('storeTableName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->pageCriteria, 'cms_page_store');
        $reflectionProperty = $reflection->getProperty('linkFieldName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->pageCriteria, 'page_id');

        $this->pageCriteria->expects($this->once())
            ->method('getTable')
            ->with('cms_page_store')
            ->will($this->returnValue('table-name'));
        $this->pageCriteria->expects($this->once())
            ->method('getMappedField')
            ->with('store')
            ->will($this->returnValue('mapped-field-result'));
        $this->selectMock->expects($this->once())
            ->method('join')
            ->with(
                ['store_table' => 'table-name'],
                'main_table.page_id = store_table.page_id',
                []
            )->will($this->returnSelf());
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with('main_table.page_id');
        $this->pageCriteria->expects($this->once())
            ->method('getConditionSql')
            ->with('mapped-field-result', ['in' => [1]])
            ->will($this->returnValue('condition-sql-result'));
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('condition-sql-result', null, \Magento\Framework\DB\Select::TYPE_CONDITION);

        $this->pageCriteria->mapStoreFilter(1, false);
    }
}
