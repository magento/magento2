<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\DataSource;

/**
 * Class PageCollectionTest
 */
class PageCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Resource\PageCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaMock;

    /**
     * @var \Magento\Cms\Model\PageRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \Magento\Cms\Model\DataSource\PageCollection
     */
    protected $pageCollection;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->repositoryMock = $this->getMock(
            'Magento\Cms\Model\PageRepository',
            [],
            [],
            '',
            false
        );

        $this->pageCollection = $objectManager->getObject(
            'Magento\Cms\Model\DataSource\PageCollection',
            [
                'repository' => $this->repositoryMock,
                'mapper' => ''
            ]
        );
    }

    /**
     * Run test addFilter method
     *
     * @param string $name
     * @param string $field
     * @param mixed $condition
     * @param string $type
     * @return void
     *
     * @dataProvider dataProviderAddFilter
     */
    public function testAddFilter($name, $field, $condition, $type)
    {
        $this->pageCollection->addFilter($name, $field, $condition, $type);
    }

    /**
     * Run test getResultCollection method
     *
     * @return void
     */
    public function testGetResultCollection()
    {
        $this->repositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->pageCollection)
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->pageCollection->getResultCollection());
    }

    /**
     * Data provider for addFilter method
     *
     * @return array
     */
    public function dataProviderAddFilter()
    {
        return [
            [
                'name' => 'test-name',
                'field' => 'store_id',
                'condition' => null,
                'type' => 'public',
            ],
            [
                'name' => 'test-name',
                'field' => 'any_field',
                'condition' => 10,
                'type' => 'private'
            ]
        ];
    }
}
