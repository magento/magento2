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
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $customizationConfig = $this->createMock(Customization::class);
        $customizationFactory = $this->createPartialMock(
            \Magento\Framework\View\Design\Theme\CustomizationFactory::class,
            ['create']
        );
        $resourceCollection = $this->createMock(Collection::class);
        $imageFactory = $this->createPartialMock(
            ImageFactory::class,
            ['create']
        );
        $themeFactory = $this->createPartialMock(
            FlyweightFactory::class,
            ['create']
        );
        $domainFactory = $this->createPartialMock(
            Factory::class,
            ['create']
        );
        $themeModelFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $validator = $this->createMock(Validator::class);
        $appState = $this->createMock(State::class);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Data::class,
            [
                'customizationFactory' => $customizationFactory,
                'customizationConfig' => $customizationConfig,
                'imageFactory' => $imageFactory,
                'resourceCollection' => $resourceCollection,
                'themeFactory' => $themeFactory,
                'domainFactory' => $domainFactory,
                'validator' => $validator,
                'appState' => $appState,
                'themeModelFactory' => $themeModelFactory
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
