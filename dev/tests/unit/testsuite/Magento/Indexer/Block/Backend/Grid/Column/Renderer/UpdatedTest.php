<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;


class UpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $bool
     * @param string $assert
     * @dataProvider renderProvider
     */
    public function testRender($bool, $assert)
    {
        $context = $this->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $model = new Updated($context);
        $obj = new \Magento\Framework\Object();
        $obj->setGetter('getValue');
        $obj->setDefault($bool);
        $obj->setValue('');
        $model->setColumn($obj);
        $result = $model->render($obj);
        $this->assertEquals($result, $assert);

    }

    public function renderProvider()
    {
        return [
            ['true', 'true'],
            ['', __('Never')]
        ];
    }
}
