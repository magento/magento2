<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\Options.
 * @magentoDbIsolation enabled
 */
class OptionsTest extends TestCase
{
    private const XML_PATH_SUFFIX_SHOW = 'customer/address/suffix_show';
    private const XML_PATH_SUFFIX_OPTIONS = 'customer/address/suffix_options';
    private const XML_PATH_PREFIX_SHOW = 'customer/address/prefix_show';
    private const XML_PATH_PREFIX_OPTIONS = 'customer/address/prefix_options';

    /**
     * @var Options
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(Options::class);
    }

    /**
     * Test suffix and prefix options
     *
     * @dataProvider optionsDataProvider
     *
     * @param string $optionType
     * @param array $showOptionConfig
     * @param array $optionValuesConfig
     * @param array $expectedOptions
     * @return void
     */
    public function testOptions(
        string $optionType,
        array $showOptionConfig,
        array $optionValuesConfig,
        array $expectedOptions
    ): void {
        $this->setConfig($showOptionConfig);
        $this->setConfig($optionValuesConfig);

        /** @var array $options */
        $options = $optionType === 'prefix'
            ? $this->model->getNamePrefixOptions()
            : $this->model->getNameSuffixOptions();

        $this->assertEquals($expectedOptions, $options);
    }

    /**
     * Set config param
     *
     * @param array $data
     * @param string|null $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    private function setConfig(
        array $data,
        ?string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = 'default'
    ): void {
        $path = array_key_first($data);
        $this->objectManager->get(MutableScopeConfigInterface::class)
            ->setValue($path, $data[$path], $scopeType, $scopeCode);
    }

    /**
     * DataProvider for testOptions()
     *
     * @return array
     */
    public static function optionsDataProvider(): array
    {
        $optionPrefixName = 'prefix';
        $optionSuffixName = 'suffix';
        $optionValues = 'v1;v2';
        $expectedValues = ['v1', 'v2'];
        $optionValuesWithBlank = ';v1;v2';
        $expectedValuesWithBlank = [' ', 'v1', 'v2'];
        $optionValuesWithTwoBlank = ';v1;v2;';
        $expectedValuesTwoBlank = [' ', 'v1', 'v2', ' '];

        return [
            'prefix_required_with_blank_option' => [
                $optionPrefixName,
                [self::XML_PATH_PREFIX_SHOW => Nooptreq::VALUE_REQUIRED],
                [self::XML_PATH_PREFIX_OPTIONS => $optionValuesWithBlank],
                $expectedValuesWithBlank,
            ],
            'prefix_required' => [
                $optionPrefixName,
                [self::XML_PATH_PREFIX_SHOW => Nooptreq::VALUE_REQUIRED],
                [self::XML_PATH_PREFIX_OPTIONS => $optionValues],
                $expectedValues,
            ],
            'prefix_required_with_two_blank_option' => [
                $optionPrefixName,
                [self::XML_PATH_PREFIX_SHOW => Nooptreq::VALUE_REQUIRED],
                [self::XML_PATH_PREFIX_OPTIONS => $optionValuesWithTwoBlank],
                $expectedValuesTwoBlank,
            ],
            'prefix_optional' => [
                $optionPrefixName,
                [self::XML_PATH_PREFIX_SHOW => Nooptreq::VALUE_OPTIONAL],
                [self::XML_PATH_PREFIX_OPTIONS => $optionValues],
                $expectedValuesWithBlank,
            ],
            'suffix_optional' => [
                $optionSuffixName,
                [self::XML_PATH_SUFFIX_SHOW => Nooptreq::VALUE_OPTIONAL],
                [self::XML_PATH_SUFFIX_OPTIONS => $optionValues],
                $expectedValuesWithBlank,
            ],
            'suffix_optional_with_blank_option' => [
                $optionSuffixName,
                [self::XML_PATH_SUFFIX_SHOW => Nooptreq::VALUE_OPTIONAL],
                [self::XML_PATH_SUFFIX_OPTIONS => $optionValuesWithBlank],
                $expectedValuesWithBlank,
            ],
            'suffix_required_with_blank_option' => [
                $optionSuffixName,
                [self::XML_PATH_SUFFIX_SHOW => Nooptreq::VALUE_OPTIONAL],
                [self::XML_PATH_SUFFIX_OPTIONS => $optionValuesWithBlank],
                $expectedValuesWithBlank,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Address::class);
    }
}
