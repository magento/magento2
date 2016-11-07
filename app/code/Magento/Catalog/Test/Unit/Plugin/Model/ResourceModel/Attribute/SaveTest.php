<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\ResourceModel\Attribute;

use Magento\Catalog\Plugin\Model\ResourceModel\Attribute\Save;
use Magento\PageCache\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var Save
     */
    protected $save;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeList;

    protected function setUp()
    {
        $this->config = $this->getMock(Config::class, ['isEnabled'], [], '', false);
        $this->typeList = $this->getMockForAbstractClass(
            TypeListInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['invalidate']
        );
        $this->subjectMock = $this->getMock(Attribute::class, [], [], '', false);
        $this->save = new Save($this->config, $this->typeList);
    }

    public function testAfterSaveWithoutInvalidate()
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->typeList->expects($this->never())
            ->method('invalidate');

        $this->assertSame($this->subjectMock, $this->save->afterSave($this->subjectMock, $this->subjectMock));
    }

    public function testAfterSave()
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->typeList->expects($this->once())
            ->method('invalidate')
            ->with('full_page');

        $this->assertSame($this->subjectMock, $this->save->afterSave($this->subjectMock, $this->subjectMock));
    }
}
