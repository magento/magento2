<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Theme\Data;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Design\Theme\CustomizationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customizationFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\Domain\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $domainFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeModelFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $customizationConfig = $this->createMock(\Magento\Theme\Model\Config\Customization::class);
        $this->customizationFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\CustomizationFactory::class,
            ['create']
        );
        $this->resourceCollection = $this->createMock(\Magento\Theme\Model\ResourceModel\Theme\Collection::class);
        $this->_imageFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\ImageFactory::class,
            ['create']
        );
        $this->themeFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\FlyweightFactory::class,
            ['create']
        );
        $this->domainFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\Domain\Factory::class,
            ['create']
        );
        $this->themeModelFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $this->validator = $this->createMock(\Magento\Framework\View\Design\Theme\Validator::class);
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);

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
