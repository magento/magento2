<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHtmlAttributes()
    {
        /** @var $fileBlock \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File */
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $collectionFactory = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $fileBlock = $helper->getObject(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File',
            ['factoryCollection' => $collectionFactory]
        );

        $this->assertContains('accept', $fileBlock->getHtmlAttributes());
        $this->assertContains('multiple', $fileBlock->getHtmlAttributes());
    }
}
