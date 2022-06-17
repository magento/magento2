<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Composer\Package\Version\VersionParser;
use Composer\Semver\Constraint\ConstraintInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Convert\DataSize;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\PhpInformation;
use Magento\Setup\Model\PhpReadinessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PhpReadinessCheckTest extends TestCase
{
    /**
     * @var MockObject|ComposerInformation
     */
    private $composerInfo;

    /**
     * @var MockObject|PhpInformation
     */
    private $phpInfo;

    /**
     * @var MockObject|VersionParser
     */
    private $versionParser;

    /**
     * Data size converter
     *
     * @var DataSize|MockObject
     */
    protected $dataSize;

    /**
     * @var PhpReadinessCheck
     */
    private $phpReadinessCheck;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->composerInfo = $this->createMock(ComposerInformation::class);
        $this->phpInfo = $this->createMock(PhpInformation::class);
        $this->versionParser = $this->createMock(VersionParser::class);
        $this->dataSize = $this->createMock(DataSize::class);
        $this->phpReadinessCheck = new PhpReadinessCheck(
            $this->composerInfo,
            $this->phpInfo,
            $this->versionParser,
            $this->dataSize
        );
    }

    /**
     * @return void
     */
    public function testCheckPhpVersionNoRequiredVersion(): void
    {
        $this->composerInfo->expects($this->once())
            ->method('getRequiredPhpVersion')
            ->willThrowException(new \Exception());
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'error' => 'phpVersionError',
                'message' => 'Cannot determine required PHP version: '
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    /**
     * @return void
     */
    public function testCheckPhpVersionPrettyVersion(): void
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $currentPhpVersion = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $this->versionParser
            ->method('parseConstraints')
            ->willReturnOnConsecutiveCalls($multipleConstraints, $currentPhpVersion);
        $this->versionParser
            ->method('normalize')
            ->willReturn('1.0');
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(true);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    /**
     * @return void
     */
    public function testCheckPhpVersionPrettyVersionFailed(): void
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $currentPhpVersion = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $this->versionParser
            ->method('parseConstraints')
            ->willReturnOnConsecutiveCalls($multipleConstraints, $currentPhpVersion);
        $this->versionParser
            ->method('normalize')
            ->willReturn('1.0');
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(false);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    /**
     * @return void
     */
    private function setUpNoPrettyVersionParser(): void
    {
        $multipleConstraints = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $currentPhpVersion = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $this->versionParser
            ->method('parseConstraints')
            ->willReturnOnConsecutiveCalls($multipleConstraints, $currentPhpVersion);
        $this->versionParser
            ->method('normalize')
            ->willReturn('1.0');
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(true);
    }

    /**
     * @return void
     */
    public function testCheckPhpVersion(): void
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');

        $this->setUpNoPrettyVersionParser();
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    /**
     * @return void
     */
    public function testCheckPhpVersionFailed(): void
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $currentPhpVersion = $this->getMockForAbstractClass(
            ConstraintInterface::class,
            [],
            '',
            false
        );
        $this->versionParser
            ->method('parseConstraints')
            ->willReturnOnConsecutiveCalls($multipleConstraints, $currentPhpVersion);
        $this->versionParser
            ->method('normalize')
            ->willReturn('1.0');
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(false);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    /**
     * @return void
     */
    public function testCheckPhpSettings(): void
    {
        $this->phpInfo->expects($this->once())->method('getCurrent')->willReturn(['xdebug']);
        $this->phpInfo->expects($this->once())->method('getRequiredMinimumXDebugNestedLevel')->willReturn(50);
        $xdebugMessage = sprintf(
            'Your current setting of xdebug.max_nesting_level=%d.
                 Magento 2 requires it to be set to %d or more.
                 Edit your config, restart web server, and try again.',
            100,
            50
        );

        $rawPostMessage = sprintf(
            'Your PHP Version is %s, but always_populate_raw_post_data = -1.
 	        $HTTP_RAW_POST_DATA is deprecated from PHP 5.6 onwards and will be removed in PHP 7.0.
 	        This will stop the installer from running.
	        Please open your php.ini file and set always_populate_raw_post_data to -1.
 	        If you need more help please call your hosting provider.',
            PHP_VERSION
        );
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'xdebug_max_nesting_level' => [
                    'message' => $xdebugMessage,
                    'error' => false
                ],
                'missed_function_imagecreatefromjpeg' => [
                    'message' => 'You must have installed GD library with --with-jpeg-dir=DIR option.',
                    'helpUrl' => 'http://php.net/manual/en/image.installation.php',
                    'error' => false
                ]
            ]
        ];
        if (!$this->isPhp7OrHhvm()) {
            $this->setUpNoPrettyVersionParser();
            $expected['data']['always_populate_raw_post_data'] = [
                'message' => $rawPostMessage,
                'helpUrl' => 'http://php.net/manual/en/ini.core.php#ini.always-populate-settings-data',
                'error' => false
            ];
        }
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    /**
     * @return void
     */
    public function testCheckPhpSettingsFailed(): void
    {
        $this->phpInfo->expects($this->once())->method('getCurrent')->willReturn(['xdebug']);
        $this->phpInfo->expects($this->once())->method('getRequiredMinimumXDebugNestedLevel')->willReturn(200);
        $xdebugMessage = sprintf(
            'Your current setting of xdebug.max_nesting_level=%d.
                 Magento 2 requires it to be set to %d or more.
                 Edit your config, restart web server, and try again.',
            100,
            200
        );

        $rawPostMessage = sprintf(
            'Your PHP Version is %s, but always_populate_raw_post_data = -1.
 	        $HTTP_RAW_POST_DATA is deprecated from PHP 5.6 onwards and will be removed in PHP 7.0.
 	        This will stop the installer from running.
	        Please open your php.ini file and set always_populate_raw_post_data to -1.
 	        If you need more help please call your hosting provider.',
            PHP_VERSION
        );
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'xdebug_max_nesting_level' => [
                    'message' => $xdebugMessage,
                    'error' => true
                ],
                'missed_function_imagecreatefromjpeg' => [
                    'message' => 'You must have installed GD library with --with-jpeg-dir=DIR option.',
                    'helpUrl' => 'http://php.net/manual/en/image.installation.php',
                    'error' => false
                ]
            ]
        ];
        if (!$this->isPhp7OrHhvm()) {
            $this->setUpNoPrettyVersionParser();
            $expected['data']['always_populate_raw_post_data'] = [
                'message' => $rawPostMessage,
                'helpUrl' => 'http://php.net/manual/en/ini.core.php#ini.always-populate-settings-data',
                'error' => false
            ];
        }
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    /**
     * @return void
     */
    public function testCheckPhpSettingsNoXDebug(): void
    {
        $this->phpInfo->expects($this->once())->method('getCurrent')->willReturn([]);

        $rawPostMessage = sprintf(
            'Your PHP Version is %s, but always_populate_raw_post_data = -1.
 	        $HTTP_RAW_POST_DATA is deprecated from PHP 5.6 onwards and will be removed in PHP 7.0.
 	        This will stop the installer from running.
	        Please open your php.ini file and set always_populate_raw_post_data to -1.
 	        If you need more help please call your hosting provider.',
            PHP_VERSION
        );
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => []
        ];
        if (!$this->isPhp7OrHhvm()) {
            $this->setUpNoPrettyVersionParser();
            $expected['data'] = [
                'always_populate_raw_post_data' => [
                    'message' => $rawPostMessage,
                    'helpUrl' => 'http://php.net/manual/en/ini.core.php#ini.always-populate-settings-data',
                    'error' => false
                ]
            ];
        }

        $expected['data']['missed_function_imagecreatefromjpeg'] = [
            'message' => 'You must have installed GD library with --with-jpeg-dir=DIR option.',
            'helpUrl' => 'http://php.net/manual/en/image.installation.php',
            'error' => false
        ];

        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    /**
     * @return void
     */
    public function testCheckPhpSettingsMemoryLimitError(): void
    {
        $this->dataSize->expects($this->any())->method('convertSizeToBytes')->willReturnMap(
            [
                ['512M', 512],
                ['756M', 756],
                ['2G', 2048]
            ]
        );

        $rawPostMessage =
                'Your current PHP memory limit is 512M.
                 Magento 2 requires it to be set to 756M or more.
                 As a user with root privileges, edit your php.ini file to increase memory_limit.
                 (The command php --ini tells you where it is located.)
                 After that, restart your web server and try again.';

        $expected['memory_limit'] = [
            'message' => $rawPostMessage,
            'error' => true,
            'warning' => false
        ];

        $this->assertEquals($expected, $this->phpReadinessCheck->checkMemoryLimit());
    }

    /**
     * @return void
     */
    public function testCheckPhpExtensionsNoRequired(): void
    {
        $this->composerInfo->expects($this->once())
            ->method('getRequiredExtensions')
            ->willThrowException(new \Exception());
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'error' => 'phpExtensionError',
                'message' => 'Cannot determine required PHP extensions: '
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    /**
     * @return void
     */
    public function testCheckPhpExtensions(): void
    {
        $this->composerInfo->expects($this->once())
            ->method('getRequiredExtensions')
            ->willReturn(['a', 'b', 'c']);
        $this->phpInfo->expects($this->once())
            ->method('getCurrent')
            ->willReturn(['a', 'b', 'c', 'd']);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'required' => ['a', 'b', 'c'],
                'missing' => []
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    /**
     * @return void
     */
    public function testCheckPhpExtensionsFailed(): void
    {
        $this->composerInfo->expects($this->once())
            ->method('getRequiredExtensions')
            ->willReturn(['a', 'b', 'c']);
        $this->phpInfo->expects($this->once())
            ->method('getCurrent')
            ->willReturn(['a', 'b']);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'required' => ['a', 'b', 'c'],
                'missing' => ['c']
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    /**
     * @return bool
     */
    protected function isPhp7OrHhvm(): bool
    {
        return version_compare(PHP_VERSION, '7.0.0-beta') >= 0 || defined('HHVM_VERSION');
    }
}

namespace Magento\Setup\Model;

/**
 * @param $param
 * @return int|string|bool
 */
function ini_get($param)
{
    if ($param === 'xdebug.max_nesting_level') {
        return 100;
    } elseif ($param === 'always_populate_raw_post_data') {
        return -1;
    } elseif ($param === 'memory_limit') {
        return '512M';
    }
    return false;
}
