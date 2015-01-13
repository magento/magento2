<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Product;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->postHelper = $this->getMock(
            'Magento\Core\Helper\PostData',
            [],
            [],
            '',
            false
        );
        $this->block = $objectManager->getObject(
            'Magento\ProductAlert\Block\Product\View',
            ['coreHelper' => $this->postHelper]
        );
    }

    public function testGetPostAction()
    {
        $this->block->setSignupUrl('someUrl');
        $this->postHelper->expects($this->once())
            ->method('getPostData')
            ->with('someUrl')
            ->will($this->returnValue('{parsedAction}'));
        $this->assertEquals('{parsedAction}', $this->block->getPostAction());
    }
}
