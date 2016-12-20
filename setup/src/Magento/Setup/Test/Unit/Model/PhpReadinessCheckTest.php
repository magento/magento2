<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\PhpReadinessCheck;

class PhpReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    private $composerInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PhpInformation
     */
    private $phpInfo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Composer\Package\Version\VersionParser
     */
    private $versionParser;

    /**
     * @var PhpReadinessCheck
     */
    private $phpReadinessCheck;

    public function setUp()
    {
        $this->composerInfo = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $this->phpInfo = $this->getMock('Magento\Setup\Model\PhpInformation', [], [], '', false);
        $this->versionParser = $this->getMock('Composer\Package\Version\VersionParser', [], [], '', false);
        $this->phpReadinessCheck = new PhpReadinessCheck($this->composerInfo, $this->phpInfo, $this->versionParser);
    }

    public function testCheckPhpVersionNoRequiredVersion()
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

    public function testCheckPhpVersionPrettyVersion()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))
            ->method('normalize')
            ->willThrowException(new \UnexpectedValueException());
        $this->versionParser->expects($this->at(2))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(3))->method('parseConstraints')->willReturn($currentPhpVersion);
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(true);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION,
            ],
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    public function testCheckPhpVersionPrettyVersionFailed()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))
            ->method('normalize')
            ->willThrowException(new \UnexpectedValueException());
        $this->versionParser->expects($this->at(2))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(3))->method('parseConstraints')->willReturn($currentPhpVersion);
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(false);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION,
            ],
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    private function setUpNoPrettyVersionParser()
    {
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(2))->method('parseConstraints')->willReturn($currentPhpVersion);
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(true);
    }

    public function testCheckPhpVersion()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');

        $this->setUpNoPrettyVersionParser();
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION,
            ],
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    public function testCheckPhpVersionFailed()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Semver\Constraint\ConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(2))->method('parseConstraints')->willReturn($currentPhpVersion);
        $multipleConstraints->expects($this->once())->method('matches')->willReturn(false);
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'required' => 1.0,
                'current' => PHP_VERSION,
            ],
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpVersion());
    }

    public function testCheckPhpSettings()
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
                    'error' => false,
                ],
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

    public function testCheckPhpSettingsFailed()
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
                    'error' => true,
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

    public function testCheckPhpSettingsNoXDebug()
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
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    public function testCheckPhpExtensionsNoRequired()
    {
        $this->composerInfo->expects($this->once())
            ->method('getRequiredExtensions')
            ->willThrowException(new \Exception());
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'error' => 'phpExtensionError',
                'message' => 'Cannot determine required PHP extensions: '
            ],
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    public function testCheckPhpExtensions()
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
                'missing' => [],
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    public function testCheckPhpExtensionsFailed()
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
                'missing' => ['c'],
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpExtensions());
    }

    /**
     * @return bool
     */
    protected function isPhp7OrHhvm()
    {
        return version_compare(PHP_VERSION, '7.0.0-beta') >= 0 || defined('HHVM_VERSION');
    }
}

namespace Magento\Setup\Model;

function ini_get($param)
{
    if ($param === 'xdebug.max_nesting_level') {
        return 100;
    } elseif ($param === 'always_populate_raw_post_data') {
        return -1;
    }
}
