<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console;

use InvalidArgumentException;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Console\InputValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Validator\Locale;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\RegexFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InputValidatorTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var InputValidator
     */
    protected $inputValidator;

    /**
     * @var Locale
     */
    protected $localeValidator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $regexFactoryMock = $this->getMockBuilder(RegexFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $regexObject = new Regex('/^[A-Za-z0-9_.]+$/');

        $regexFactoryMock->expects($this->any())->method('create')
            ->willReturn($regexObject);

        $localeObjectMock = $this->getMockBuilder(Locale::class)
            ->setMethods(['isValid'])
            ->disableOriginalConstructor()
            ->getMock();

        $localeObjectMock->expects($this->any())->method('isValid')
            ->with('en_US')
            ->willReturn(true);

        $this->inputValidator = $this->objectManagerHelper->getObject(
            InputValidator::class,
            [
                'localeValidator' => $localeObjectMock,
                'versionValidatorFactory' => $regexFactoryMock
            ]
        );
    }

    public function testValidate()
    {
        $input = $this->getMockBuilder(ArrayInput::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOption', 'getArgument'])
            ->getMock();

        $input->expects($this->atLeastOnce())->method('getArgument')->willReturn(['all']);

        $input->expects($this->atLeastOnce())->method('getOption')
            ->willReturnMap(
                [
                    [Options::AREA, ['all']],
                    [Options::EXCLUDE_AREA, ['none']],
                    [Options::THEME, ['all']],
                    [Options::EXCLUDE_THEME, ['none']],
                    [Options::EXCLUDE_LANGUAGE, ['none']],
                    [Options::CONTENT_VERSION, '12345']
                ]
            );

        /** @noinspection PhpParamsInspection */
        $this->inputValidator->validate($input);
    }

    /**
     * @covers \Magento\Deploy\Console\InputValidator::checkAreasInput()
     */
    public function testCheckAreasInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, '', ['test']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, '', ['test'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                '--area (-a) and --exclude-area cannot be used at the same time',
                $e->getMessage()
            );
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    /**
     * @covers \Magento\Deploy\Console\InputValidator::checkThemesInput()
     */
    public function testCheckThemesInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, '', ['none']),
            new InputOption(Options::THEME, null, 4, '', ['blank']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['luma'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                '--theme (-t) and --exclude-theme cannot be used at the same time',
                $e->getMessage()
            );
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testCheckLanguagesInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_AREA, '', 4, '', ['none']),
            new InputOption(Options::THEME, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['none']),
            new InputArgument(Options::LANGUAGES_ARGUMENT, 2, '', ['en_US']),
            new InputOption(Options::EXCLUDE_LANGUAGE, null, 4, '', ['all'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                '--language (-l) and --exclude-language cannot be used at the same time',
                $e->getMessage()
            );

            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testCheckVersionInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, '', ['none']),
            new InputOption(Options::THEME, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['none']),
            new InputArgument(Options::LANGUAGES_ARGUMENT, 2, '', ['en_US']),
            new InputOption(Options::EXCLUDE_LANGUAGE, null, 4, '', ['none']),
            new InputOption(Options::CONTENT_VERSION, null, 4, '', '/*!#')
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                'Argument "' .
                Options::CONTENT_VERSION
                . '" has invalid value, content version should contain only characters, digits and dots',
                $e->getMessage()
            );

            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }
}
