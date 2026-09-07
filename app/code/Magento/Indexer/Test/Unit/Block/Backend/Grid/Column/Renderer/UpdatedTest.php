<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Indexer\Block\Backend\Grid\Column\Renderer\Updated;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UpdatedTest extends TestCase
{
    /**
     * @param string $defaultValue
     * @param string $assert
     */
    #[DataProvider('renderProvider')]
    public function testRender($defaultValue, $assert)
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model = new Updated($context);
        $obj = new DataObject();
        $obj->setGetter('getValue');
        $obj->setDefault($defaultValue);
        $obj->setValue('');
        $model->setColumn($obj);
        $result = $model->render($obj);
        $this->assertEquals($result, $assert);
    }

    /**
     * @return array
     */
    public static function renderProvider()
    {
        return [
            ['true', 'true'],
            ['', __('Never')]
        ];
    }
}
