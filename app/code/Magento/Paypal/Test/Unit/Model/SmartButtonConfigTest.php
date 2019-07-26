<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Paypal\Model\ConfigFactory;

class SmartButtonConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\SmartButtonConfig
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->configMock = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $configFactoryMock */
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configFactoryMock->expects($this->once())->method('create')->willReturn($this->configMock);
        $this->model = new SmartButtonConfig(
            $this->localeResolverMock,
            $configFactoryMock,
            $this->getDefaultStyles(),
            $this->getAllowedFundings()
        );
    }

    /**
     * @param string $page
     * @param string $locale
     * @param string $disallowedFundings
     * @param string $layout
     * @param string $size
     * @param string $shape
     * @param string $label
     * @param string $color
     * @param string $installmentPeriodLabel
     * @param string $installmentPeriodLocale
     * @param array $expected
     * @dataProvider getConfigDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetConfig(
        string $page,
        string $locale,
        bool $isCustomize,
        ?string $disallowedFundings,
        string $layout,
        string $size,
        string $shape,
        string $label,
        string $color,
        string $installmentPeriodLabel,
        string $installmentPeriodLocale,
        array $expected = []
    ) {
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn($locale);
        $this->configMock->expects($this->any())->method('getValue')->will($this->returnValueMap([
            ['merchant_id', null, 'merchant'],
            ['sandbox_flag', null, true],
            ['disable_funding_options', null, $disallowedFundings],
            ["{$page}_page_button_customize", null, $isCustomize],
            ["{$page}_page_button_layout", null, $layout],
            ["{$page}_page_button_size", null, $size],
            ["{$page}_page_button_color", null, $color],
            ["{$page}_page_button_shape", null, $shape],
            ["{$page}_page_button_label", null, $label],
            [$page . '_page_button_' . $installmentPeriodLocale . '_installment_period', null, $installmentPeriodLabel]
        ]));

        self::assertEquals($expected, $this->model->getConfig($page));
    }

    public function getConfigDataProvider()
    {
        return include __DIR__ . '/_files/expected_config.php';
    }

    /**
     * @return array
     */
    private function getDefaultStyles()
    {
        return include __DIR__ . '/_files/default_styles.php';
    }

    /**
     * @return array
     */
    private function getAllowedFundings()
    {
        return include __DIR__ . '/_files/allowed_fundings.php';
    }
}
