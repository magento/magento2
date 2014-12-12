<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model\Layer\Filter\Item;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class DataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder
     */
    protected $dataBuilder;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataBuilder = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\Item\DataBuilder',
            []
        );
    }

    public function testBuild()
    {
        $data = [
            [
                'label' => 'Test label',
                'value' => 34,
                'count' => 21235,
            ],
            [
                'label' => 'New label for test',
                'value' => 2344,
                'count' => 122,
            ],
        ];

        foreach ($data as $item) {
            $this->dataBuilder->addItemData($item['label'], $item['value'], $item['count']);
        }

        $actualData = $this->dataBuilder->build();
        $this->assertEquals($data, $actualData);
    }
}
