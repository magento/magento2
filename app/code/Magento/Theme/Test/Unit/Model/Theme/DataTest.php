<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\Domain\Factory;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\Theme\ImageFactory;
use Magento\Framework\View\Design\Theme\Validator;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme\Data;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $model;

    protected function setUp(): void
    {
        $customizationConfig = $this->createMock(Customization::class);
        $this->customizationFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\CustomizationFactory::class,
            ['create']
        );
        $this->resourceCollection = $this->createMock(Collection::class);
        $this->_imageFactory = $this->createPartialMock(
            ImageFactory::class,
            ['create']
        );
        $this->themeFactory = $this->createPartialMock(
            FlyweightFactory::class,
            ['create']
        );
        $this->domainFactory = $this->createPartialMock(
            Factory::class,
            ['create']
        );
        $this->themeModelFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $this->validator = $this->createMock(Validator::class);
        $this->appState = $this->createMock(State::class);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Data::class,
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

        $this->model = $objectManagerHelper->getObject(Data::class, $arguments);
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
