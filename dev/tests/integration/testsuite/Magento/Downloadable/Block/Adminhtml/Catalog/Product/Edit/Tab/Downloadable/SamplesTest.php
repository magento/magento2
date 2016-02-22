<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class SamplesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUploadButtonsHtml()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples'
        );
        \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\LinksTest::performUploadButtonTest(
            $block
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSampleData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            new \Magento\Framework\DataObject(['type_id' => 'simple'])
        );
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples'
        );
        $this->assertEmpty($block->getSampleData());
    }

    /**
     * Get Samples Title for simple/virtual/downloadable product
     *
     * @magentoConfigFixture current_store catalog/downloadable/samples_title Samples Title Test
     * @magentoAppIsolation enabled
     * @dataProvider productSamplesTitleDataProvider
     *
     * @param string $productType
     * @param string $samplesTitle
     * @param string $expectedResult
     */
    public function testGetSamplesTitle($productType, $samplesTitle, $expectedResult)
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_product',
            new \Magento\Framework\DataObject(
                [
                    'type_id' => $productType,
                    'id' => '1',
                    'samples_title' => $samplesTitle,
                ]
            )
        );
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples'
        );
        $this->assertEquals($expectedResult, $block->getSamplesTitle());
    }

    /**
     * Data Provider with product types
     *
     * @return array
     */
    public function productSamplesTitleDataProvider()
    {
        return [
            ['simple', null, 'Samples Title Test'],
            ['simple', 'Samples Title', 'Samples Title Test'],
            ['virtual', null, 'Samples Title Test'],
            ['virtual', 'Samples Title', 'Samples Title Test'],
            ['downloadable', null, null],
            ['downloadable', 'Samples Title', 'Samples Title']
        ];
    }
}
