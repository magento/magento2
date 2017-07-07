<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Theme\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $model;

    protected function setUp()
    {
        $customizationConfig = $this->getMock(\Magento\Theme\Model\Config\Customization::class, [], [], '', false);
        $this->customizationFactory = $this->getMock(
            \Magento\Framework\View\Design\Theme\CustomizationFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resourceCollection = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->_imageFactory = $this->getMock(
            \Magento\Framework\View\Design\Theme\ImageFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeFactory = $this->getMock(
            \Magento\Framework\View\Design\Theme\FlyweightFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->domainFactory = $this->getMock(
            \Magento\Framework\View\Design\Theme\Domain\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeModelFactory = $this->getMock(
            \Magento\Theme\Model\ThemeFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->validator = $this->getMock(\Magento\Framework\View\Design\Theme\Validator::class, [], [], '', false);
        $this->appState = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            \Magento\Theme\Model\Theme\Data::class,
            [
                'customizationFactory' => $this->customizationFactory,
                'customizationConfig' => $customizationConfig,
                'imageFactory' => $this->_imageFactory,
                'resourceCollection' => $this->resourceCollection,
                'themeFactory' => $this->themeFactory,
                'domainFactory' => $this->domainFactory,
                'validator' => $this->validator,
                'appState' => $this->appState,
                'themeModelFactory' => $this->themeModelFactory
            ]
        );

        $this->model = $objectManagerHelper->getObject(\Magento\Theme\Model\Theme\Data::class, $arguments);
    }

    /**
     * @test
     * @return void
     */
    public function testGetArea()
    {
        $area = Area::AREA_FRONTEND;
        $this->model->setArea($area);
        $this->assertEquals($area, $this->model->getArea());
    }
}
