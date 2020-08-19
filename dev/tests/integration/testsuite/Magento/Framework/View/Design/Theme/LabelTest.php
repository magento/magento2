<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

class LabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Label
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Design\Theme\Label::class
        );
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Label::getLabelsCollection
     */
    public function testGetLabelsCollection()
    {
        /** @var $expectedCollection \Magento\Theme\Model\ResourceModel\Theme\Collection */
        $expectedCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Design\Theme\Label\ListInterface::class
        );

        $expectedItemsCount = count($expectedCollection->getLabels());

        $labelsCollection = $this->_model->getLabelsCollection();
        $this->assertEquals($expectedItemsCount, count($labelsCollection));

        $labelsCollection = $this->_model->getLabelsCollection('-- Please Select --');
        $this->assertEquals(++$expectedItemsCount, count($labelsCollection));
    }
}
