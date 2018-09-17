<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend\Grid\Column\Renderer;

class UpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $defaultValue
     * @param string $assert
     * @dataProvider renderProvider
     */
    public function testRender($defaultValue, $assert)
    {
        $context = $this->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $model = new \Magento\Indexer\Block\Backend\Grid\Column\Renderer\Updated($context);
        $obj = new \Magento\Framework\DataObject();
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
    public function renderProvider()
    {
        return [
            ['true', 'true'],
            ['', __('Never')]
        ];
    }
}
