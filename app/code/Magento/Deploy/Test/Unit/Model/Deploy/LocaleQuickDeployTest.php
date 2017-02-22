<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Deploy;

use Magento\Deploy\Model\Deploy\DeployInterface;
use Magento\Deploy\Model\Deploy\LocaleQuickDeploy;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use \Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\Filesystem;

class LocaleQuickDeployTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputMock;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirectoryMock;

    protected function setUp()
    {
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->setMethods(['writeln'])
            ->getMockForAbstractClass();

        $this->staticDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->setMethods(['createSymlink', 'getAbsolutePath', 'getRelativePath', 'copyFile', 'readRecursively'])
            ->getMockForAbstractClass();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Deploy base locale must be set for Quick Deploy
     */
    public function testDeployWithoutBaseLocale()
    {
        $this->getModel()->deploy('adminhtml', 'Magento/backend', 'en_US');
    }

    public function testDeployWithSymlinkStrategy()
    {
        $area = 'adminhtml';
        $themePath = 'Magento/backend';
        $locale = 'uk_UA';
        $baseLocal = 'en_US';

        $this->staticDirectoryMock->expects(self::exactly(2))
            ->method('createSymlink')
            ->withConsecutive(
                ['adminhtml/Magento/backend/en_US', 'adminhtml/Magento/backend/uk_UA'],
                ['_requirejs/adminhtml/Magento/backend/en_US', '_requirejs/adminhtml/Magento/backend/uk_UA']
            );

        $model = $this->getModel([
            DeployInterface::DEPLOY_BASE_LOCALE => $baseLocal,
            Options::SYMLINK_LOCALE => 1,
        ]);
        $model->deploy($area, $themePath, $locale);
    }

    public function testDeployWithCopyStrategy()
    {

        $area = 'adminhtml';
        $themePath = 'Magento/backend';
        $locale = 'uk_UA';
        $baseLocal = 'en_US';

        $this->staticDirectoryMock->expects(self::never())->method('createSymlink');
        $this->staticDirectoryMock->expects(self::exactly(2))->method('readRecursively')->willReturnMap([
            ['adminhtml/Magento/backend/en_US', [$baseLocal . 'file1', $baseLocal . 'dir']],
            [RequireJsConfig::DIR_NAME  . '/adminhtml/Magento/backend/en_US', [$baseLocal . 'file2']]
        ]);
        $this->staticDirectoryMock->expects(self::exactly(3))->method('isFile')->willReturnMap([
            [$baseLocal . 'file1', true],
            [$baseLocal . 'dir', false],
            [$baseLocal . 'file2', true],
        ]);
        $this->staticDirectoryMock->expects(self::exactly(2))->method('copyFile')->withConsecutive(
            [$baseLocal . 'file1', $locale . 'file1', null],
            [$baseLocal . 'file2', $locale . 'file2', null]
        );

        $model = $this->getModel([
            DeployInterface::DEPLOY_BASE_LOCALE => $baseLocal,
            Options::SYMLINK_LOCALE => 0,
        ]);
        $model->deploy($area, $themePath, $locale);
    }

    /**
     * @param array $options
     * @return LocaleQuickDeploy
     */
    private function getModel($options = [])
    {
        $filesystemMock = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $filesystemMock->expects(self::any())->method('getDirectoryWrite')->willReturn($this->staticDirectoryMock);
        return (new ObjectManager($this))->getObject(
            LocaleQuickDeploy::class,
            [
                'output' => $this->outputMock,
                'filesystem' => $filesystemMock,
                'options' => $options
            ]
        );
    }
}
