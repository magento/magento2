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

namespace Magento\Catalog\Service\V1\Product\CustomOptions;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionTypeBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMetadataReaderMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->configMock = $this->getMock('Magento\Catalog\Model\ProductOptions\ConfigInterface');
        $this->optionTypeBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\CustomOptions\Data\OptionTypeBuilder',
            [],
            [],
            '',
            false
        );

        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Model\ProductRepository', [], [], '', false);

        $this->optionBuilderMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\OptionBuilder', [], [], '', false);
        $this->optionMetadataReaderMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ReaderInterface');
        $this->service = $helper->getObject(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\ReadService',
            [
                'productOptionConfig' => $this->configMock,
                'optionTypeBuilder' => $this->optionTypeBuilderMock,
                'optionBuilder' => $this->optionBuilderMock,
                'productRepository' => $this->productRepositoryMock,
                'optionMetadataReader' => $this->optionMetadataReaderMock
            ]
        );
    }

    public function testGetTypes()
    {
        $config = [
            [
                'label' => 'group label 1',
                'types' => [
                    [
                        'label' => 'label 1.1',
                        'name' => 'name 1.1',
                        'disabled' => false
                    ],
                ]
            ],
            [
                'label' => 'group label 2',
                'types' => [
                    [
                        'label' => 'label 2.2',
                        'name' => 'name 2.2',
                        'disabled' => true
                    ],
                ]
            ],
        ];

        $this->configMock->expects($this->once())->method('getAll')->will($this->returnValue($config));

        $expectedConfig = [
            'label' => 'label 1.1',
            'code' => 'name 1.1',
            'group' => 'group label 1'
        ];

        $object = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\OptionType', [], [], '', false);
        $this->optionTypeBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($expectedConfig)
            ->will($this->returnSelf());

        $this->optionTypeBuilderMock->expects($this->once())->method('create')->will($this->returnValue($object));

        $this->assertEquals([$object], $this->service->getTypes());
    }

    public function testGetList()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $value = [
            'price_type' => 'fixed',
            'sku' => 'sku1',
            'max_characters' => 10
        ];
        $options[] = [
            Data\Option::OPTION_ID => 10,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
            Data\Option::METADATA => $value
        ];
        $methods = array('getId', 'getTitle', 'getType', 'getIsRequire', 'getSortOrder', '__wakeup');
        $optionMock = $this->getMock('\Magento\Catalog\Model\Product\Option', $methods, [], '', false);
        $optionData = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $productMock
            ->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(array($optionMock)));
        $optionMock->expects($this->once())->method('getId')->will($this->returnValue(10));
        $optionMock->expects($this->once())->method('getTitle')->will($this->returnValue('Some title'));
        $optionMock->expects($this->once())->method('getType')->will($this->returnValue('text'));
        $optionMock->expects($this->once())->method('getIsRequire')->will($this->returnValue(true));
        $optionMock->expects($this->once())->method('getSortOrder')->will($this->returnValue(10));
        $this->optionMetadataReaderMock
            ->expects($this->once())
            ->method('read')
            ->with($optionMock)
            ->will($this->returnValue($value));
        $this->optionBuilderMock
            ->expects($this->once())
            ->method('populateWithArray')
            ->with($options[0])
            ->will($this->returnSelf());
        $this->optionBuilderMock->expects($this->once())->method('create')->will($this->returnValue($optionData));

        $this->assertEquals(array($optionData), $this->service->getList('product_sku'));
    }
}
