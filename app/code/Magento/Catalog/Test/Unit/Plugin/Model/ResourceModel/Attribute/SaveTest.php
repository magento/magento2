<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Catalog\Plugin\Model\ResourceModel\Attribute\Save;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    /**
     * @var Attribute|MockObject
     */
    private $subjectMock;

    /**
     * @var Save
     */
    protected $save;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var TypeListInterface|MockObject
     */
    protected $typeList;

    protected function setUp(): void
    {
        $this->config = $this->createPartialMock(Config::class, ['isEnabled']);
        $this->typeList = $this->getMockForAbstractClass(
            TypeListInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['invalidate']
        );
        $this->subjectMock = $this->createMock(Attribute::class);
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
