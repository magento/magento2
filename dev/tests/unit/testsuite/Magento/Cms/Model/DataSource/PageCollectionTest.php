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
namespace Magento\Cms\Model\DataSource;

/**
 * Class PageCollectionTest
 */
class PageCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Api\PageCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaMock;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
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

        $this->criteriaMock = $this->getMockForAbstractClass(
            'Magento\Cms\Api\PageCriteriaInterface',
            [],
            '',
            false,
            true,
            true,
            ['addStoreFilter', 'addFilter', 'setFirstStoreFlag']
        );
        $this->repositoryMock = $this->getMockForAbstractClass(
            'Magento\Cms\Api\PageRepositoryInterface',
            [],
            '',
            false
        );

        $this->criteriaMock->expects($this->once())
            ->method('setFirstStoreFlag')
            ->with(true);

        $this->pageCollection = $objectManager->getObject(
            'Magento\Cms\Model\DataSource\PageCollection',
            [
                'criteria' => $this->criteriaMock,
                'repository' => $this->repositoryMock
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
        if ($field === 'store_id') {
            $this->criteriaMock->expects($this->once())
                ->method('addStoreFilter')
                ->with($condition, false);
        } else {
            $this->criteriaMock->expects($this->once())
                ->method('addFilter')
                ->with($name, $field, $condition, $type);
        }

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
            ->with($this->criteriaMock)
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
                'type' => 'public'
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
