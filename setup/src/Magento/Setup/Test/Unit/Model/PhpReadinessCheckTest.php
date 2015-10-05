<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
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
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
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
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
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
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
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

    public function testCheckPhpVersion()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(2))->method('parseConstraints')->willReturn($currentPhpVersion);
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

    public function testCheckPhpVersionFailed()
    {
        $this->composerInfo->expects($this->once())->method('getRequiredPhpVersion')->willReturn('1.0');
        $multipleConstraints = $this->getMockForAbstractClass(
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
            [],
            '',
            false
        );
        $this->versionParser->expects($this->at(0))->method('parseConstraints')->willReturn($multipleConstraints);
        $this->versionParser->expects($this->at(1))->method('normalize')->willReturn('1.0');
        $currentPhpVersion = $this->getMockForAbstractClass(
            'Composer\Package\LinkConstraint\LinkConstraintInterface',
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
        $message = sprintf(
            'Your current setting of xdebug.max_nesting_level=%d.
                 Magento 2 requires it to be set to %d or more.
                 Edit your config, restart web server, and try again.',
            100,
            50
        );
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
            'data' => [
                'xdebug_max_nesting_level' => [
                    'message' => $message,
                    'error' => false,
                ]
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    public function testCheckPhpSettingsFailed()
    {
        $this->phpInfo->expects($this->once())->method('getCurrent')->willReturn(['xdebug']);
        $this->phpInfo->expects($this->once())->method('getRequiredMinimumXDebugNestedLevel')->willReturn(200);
        $message = sprintf(
            'Your current setting of xdebug.max_nesting_level=%d.
                 Magento 2 requires it to be set to %d or more.
                 Edit your config, restart web server, and try again.',
            100,
            200
        );
        $expected = [
            'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
            'data' => [
                'xdebug_max_nesting_level' => [
                    'message' => $message,
                    'error' => true,
                ]
            ]
        ];
        $this->assertEquals($expected, $this->phpReadinessCheck->checkPhpSettings());
    }

    public function testCheckPhpSettingsNoXDebug()
    {
        $this->phpInfo->expects($this->once())->method('getCurrent')->willReturn([]);
        $expected = ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, 'data' => []];
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
}

namespace Magento\Setup\Model;

function ini_get()
{
    return 100;
}
