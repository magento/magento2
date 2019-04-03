<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Test\Unit\Console;

use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\RegexFactory;
use PHPUnit\Framework\TestCase;
use Magento\Deploy\Console\InputValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\Validator\Locale;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\ArrayInput;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class InputValidatorTest
 * @package Magento\Deploy\Test\Unit\Console
 *  * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @throws \Zend_Validate_Exception
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $regexFactoryMock = $this->getMockBuilder(RegexFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $regexObject = new Regex('/^[A-Za-z0-9_.]+$/');

        $regexFactoryMock->expects($this->any())->method('create')
            ->willReturn($regexObject);

        $localeObjectMock = $this->getMockBuilder(Locale::class)->setMethods(['isValid'])
            ->disableOriginalConstructor()
            ->getMock();

        $localeObjectMock->expects($this->any())->method('isValid')
            ->with('en_US')
            ->will($this->returnValue(true));

        $this->inputValidator = $this->objectManagerHelper->getObject(
            InputValidator::class,
            [
                'localeValidator' => $localeObjectMock,
                'versionValidatorFactory' => $regexFactoryMock
            ]
        );
    }

    /**
     * @throws \Zend_Validate_Exception
     */
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
            new InputOption(Options::AREA, null, 4, null, ['test']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, null, ['test'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertContains('--area (-a) and --exclude-area cannot be used at the same time', $e->getMessage());
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    /**
     * @covers \Magento\Deploy\Console\InputValidator::checkThemesInput()
     */
    public function testCheckThemesInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, null, ['all']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, null, ['none']),
            new InputOption(Options::THEME, null, 4, '', ['blank']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['luma'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertContains('--theme (-t) and --exclude-theme cannot be used at the same time', $e->getMessage());
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testCheckLanguagesInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, null, ['all']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, null, ['none']),
            new InputOption(Options::THEME, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['none']),
            new InputArgument(Options::LANGUAGES_ARGUMENT, null, 4, ['en_US']),
            new InputOption(Options::EXCLUDE_LANGUAGE, null, 4, '', ['all'])
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertContains(
                '--language (-l) and --exclude-language cannot be used at the same time',
                $e->getMessage()
            );

            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testCheckVersionInputException()
    {
        $options = [
            new InputOption(Options::AREA, null, 4, null, ['all']),
            new InputOption(Options::EXCLUDE_AREA, null, 4, null, ['none']),
            new InputOption(Options::THEME, null, 4, '', ['all']),
            new InputOption(Options::EXCLUDE_THEME, null, 4, '', ['none']),
            new InputArgument(Options::LANGUAGES_ARGUMENT, null, 4, ['en_US']),
            new InputOption(Options::EXCLUDE_LANGUAGE, null, 4, '', ['none']),
            new InputOption(Options::CONTENT_VERSION, null, 4, '', '/*!#')
        ];

        $inputDefinition = new InputDefinition($options);

        try {
            $this->inputValidator->validate(
                new ArrayInput([], $inputDefinition)
            );
        } catch (\Exception $e) {
            $this->assertContains(
                'Argument "' .
                Options::CONTENT_VERSION
                . '" has invalid value, content version should contain only characters, digits and dots',
                $e->getMessage()
            );

            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }
}
