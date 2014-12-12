<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager;

class SortbyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Sortby
     */
    private $model;

    public function testGetAllOptions()
    {
        $validResult = [['label' => __('Position'), 'value' => 'position'], ['label' => __('fl'), 'value' => 'fc']];
        $this->assertEquals($validResult, $this->model->getAllOptions());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Category\Attribute\Source\Sortby',
            [
                'catalogConfig' => $this->getMockedConfig()
            ]
        );
    }

    /**
     * @return \Magento\Catalog\Model\Config
     */
    private function getMockedConfig()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Config');
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getAttributesUsedForSortBy')
            ->will($this->returnValue([['frontend_label' => 'fl', 'attribute_code' => 'fc']]));

        return $mock;
    }
}
