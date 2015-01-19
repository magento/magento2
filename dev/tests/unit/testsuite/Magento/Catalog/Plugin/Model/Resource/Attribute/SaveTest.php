<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\Resource\Attribute;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Plugin\Model\Resource\Attribute\Save */
    protected $save;

    /** @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $typeList;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMock();
        $this->typeList = $this->getMockBuilder('Magento\Framework\App\Cache\TypeListInterface')
            ->disableOriginalConstructor()
            ->setMethods(['invalidate'])
            ->getMockForAbstractClass();

        $this->save = new Save($this->config, $this->typeList);
    }

    public function testAroundSaveWithoutInvalidate()
    {
        $subject = $this->getMockBuilder('Magento\Catalog\Model\Resource\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $self = $this;
        $proceed = function ($object) use ($self, $attribute) {
            $self->assertEquals($object, $attribute);
        };

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->typeList->expects($this->never())
            ->method('invalidate');

        $this->save->aroundSave($subject, $proceed, $attribute);
    }

    public function testAroundSave()
    {
        $subject = $this->getMockBuilder('Magento\Catalog\Model\Resource\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $self = $this;
        $proceed = function ($object) use ($self, $attribute) {
            $self->assertEquals($object, $attribute);
        };

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->typeList->expects($this->once())
            ->method('invalidate')
            ->with('full_page');

        $this->save->aroundSave($subject, $proceed, $attribute);
    }
}
