<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model\Indexer\Category\Product;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Catalog\Plugin\Model\Indexer\Category\Product\Execute;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExecuteTest extends TestCase
{
    use MockCreationTrait;
    /** @var Execute */
    protected $execute;

    /** @var Config|MockObject */
    protected $config;

    /** @var TypeListInterface|MockObject */
    protected $typeList;

    protected function setUp(): void
    {
        $this->config = $this->createPartialMock(Config::class, ['isEnabled']);
        $this->typeList = $this->createMock(TypeListInterface::class);
        $this->execute = new Execute($this->config, $this->typeList);
    }

    public function testAfterExecute()
    {
        $subject = $this->createMock(AbstractAction::class);
        $result = $this->createMock(AbstractAction::class);

        $this->config->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->typeList->expects($this->never())->method('invalidate');

        $actualResult = $this->execute->afterExecute($subject, $result);
        
        $this->assertEquals($result, $actualResult);
    }

    public function testAfterExecuteInvalidate()
    {
        $subject = $this->createMock(AbstractAction::class);
        $result = $this->createMock(AbstractAction::class);

        $this->config->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->once())->method('invalidate')->with('full_page');

        $actualResult = $this->execute->afterExecute($subject, $result);
        
        $this->assertEquals($result, $actualResult);
    }
}
