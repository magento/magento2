<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Block;

use Magento\Translation\Block\Js;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Js
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configMock = $this->getMockBuilder('Magento\Translation\Model\Js\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject('Magento\Translation\Block\Js', ['config' => $this->configMock]);
    }

    public function testIsDictionaryStrategy()
    {
        $this->configMock->expects($this->once())
            ->method('dictionaryEnabled')
            ->willReturn(true);
        $this->assertTrue($this->model->dictionaryEnabled());
    }
}
