<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType\Builder;

/**
 * Class ConfigJsonTest
 */
class ConfigJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigJson
     */
    protected $builder;

    public function testToJson()
    {
        $this->builder = new ConfigJson();
        $result = ['name' => 'resultName', 'parent_name' => 'resultParentName'];
        $configurationMock = $this->getMock(
            'Magento\Ui\Context\Configuration',
            ['getData', 'getName', 'getParentName'],
            [],
            '',
            false
        );
        $configurationMock->expects($this->once())->method('getData')->willReturn($result);
        $configurationMock->expects($this->once())->method('getName')->willReturn($result['name']);
        $configurationMock->expects($this->once())->method('getParentName')->willReturn($result['parent_name']);
        $this->assertEquals(json_encode($result), $this->builder->toJson($configurationMock));
    }
}
