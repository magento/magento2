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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class CreditmemoListTest
 */
class CreditmemoListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\CreditmemoList
     */
    protected $creditmemoList;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMapperMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * SetUp
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditmemoRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\CreditmemoRepository',
            ['find'],
            [],
            '',
            false
        );
        $this->creditmemoMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CreditmemoMapper',
            [],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CreditmemoSearchResultsBuilder',
            ['setItems', 'setSearchCriteria', 'create', 'setTotalCount'],
            [],
            '',
            false
        );
        $this->searchCriteriaMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Creditmemo',
            [],
            [],
            '',
            false
        );
        $this->creditmemoList = new CreditmemoList(
            $this->creditmemoRepositoryMock,
            $this->creditmemoMapperMock,
            $this->searchResultsBuilderMock
        );
    }

    /**
     * Test creditmemo list service
     *
     * @return void
     */
    public function testCreditmemo()
    {
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue([$this->creditmemoMock]));
        $this->creditmemoMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->creditmemoMock))
            ->will($this->returnValue($this->dataObjectMock));
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo([$this->dataObjectMock]))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setTotalCount')
            ->with($this->equalTo(count($this->creditmemoMock)))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('expected-result'));
        $this->assertEquals('expected-result', $this->creditmemoList->invoke($this->searchCriteriaMock));
    }
}
