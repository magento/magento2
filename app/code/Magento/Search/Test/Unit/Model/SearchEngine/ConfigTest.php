<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\SearchEngine;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDeclaredFeatures()
    {
        $dataStorage = $this->getMock('Magento\Search\Model\SearchEngine\Config\Data', [], [], '', false);
        $config = new \Magento\Search\Model\SearchEngine\Config($dataStorage);
        $dataStorage->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(['synonyms'], $config->getDeclaredFeatures('mysql'));
    }
}
