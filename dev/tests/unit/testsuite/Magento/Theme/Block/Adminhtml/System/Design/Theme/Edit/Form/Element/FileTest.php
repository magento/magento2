<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
