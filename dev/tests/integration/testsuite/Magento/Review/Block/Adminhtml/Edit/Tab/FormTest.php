<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(
            \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form::class,
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\View\LayoutInterface::class
            )->createBlock(
                \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form::class
            )
        );
    }
}
