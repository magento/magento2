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
            new \Magento\Framework\Object(array('type_id' => 'simple'))
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
            new \Magento\Framework\Object(
                array(
                    'type_id' => $productType,
                    'id' => '1',
                    'samples_title' => $samplesTitle
                )
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
        return array(
            array('simple', null, 'Samples Title Test'),
            array('simple', 'Samples Title', 'Samples Title Test'),
            array('virtual', null, 'Samples Title Test'),
            array('virtual', 'Samples Title', 'Samples Title Test'),
            array('downloadable', null, null),
            array('downloadable', 'Samples Title', 'Samples Title')
        );
    }
}
