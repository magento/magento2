<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Magento\Framework\App\State;

/**
 * Oyejorge adapter model
 */
class OyejorgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Oyejorge
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\Css\PreProcessor\Adapter\Oyejorge');
        $this->state = $objectManager->get('Magento\Framework\App\State');
    }

    public function testProcess()
    {
        $sourceFilePath = realpath(__DIR__ . '/../_files/oyejorge.less');
        $expectedCss = ($this->state->getMode() === State::MODE_DEVELOPER)
            ? file_get_contents(__DIR__ . '/../_files/oyejorge_dev.css')
            : file_get_contents(__DIR__ . '/../_files/oyejorge.css');
        $this->assertEquals($expectedCss, $this->model->process($sourceFilePath));
    }
}
