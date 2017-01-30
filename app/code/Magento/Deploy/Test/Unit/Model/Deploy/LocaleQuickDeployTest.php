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
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Translation\Model\Js\Config as TranslationJsConfig;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\LocalInterface as Asset;
use Magento\Framework\App\View\Asset\Publisher;

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

    /**
     * @var TranslationJsConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translationJsConfig;

    /**
     * @var TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var Asset|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var Publisher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetPublisher;

    protected function setUp()
    {
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->setMethods(['writeln', 'isVeryVerbose'])
            ->getMockForAbstractClass();

        $this->staticDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->setMethods(['createSymlink', 'getAbsolutePath', 'getRelativePath', 'copyFile', 'readRecursively'])
            ->getMockForAbstractClass();

        $this->translationJsConfig = $this->getMock(TranslationJsConfig::class, [], [], '', false);
        $this->translator = $this->getMockForAbstractClass(TranslateInterface::class, [], '', false, false, true);
        $this->assetRepo = $this->getMock(Repository::class, [], [], '', false);
        $this->asset = $this->getMockForAbstractClass(Asset::class, [], '', false, false, true);;
        $this->assetPublisher = $this->getMock(Publisher::class, [], [], '', false);
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
        $baseLocale = 'en_US';
        $baseDir = $baseLocale . 'dir';
        $file1 = 'file1';
        $file2 = 'file2';
        $baseFile1 = $baseLocale . $file1;
        $baseFile2 = $baseLocale . $file2;

        $dictionary = TranslationJsConfig::DICTIONARY_FILE_NAME;
        $baseDictionary = $baseLocale . $dictionary;


        $this->staticDirectoryMock->expects(self::never())->method('createSymlink');
        $this->staticDirectoryMock->expects(self::exactly(2))->method('readRecursively')->willReturnMap(
            [
                ['adminhtml/Magento/backend/en_US', [$baseFile1, $baseDir]],
                [RequireJsConfig::DIR_NAME  . '/adminhtml/Magento/backend/en_US', [$baseFile2, $baseDictionary]]
            ]
        );
        $this->staticDirectoryMock->expects(self::exactly(4))->method('isFile')->willReturnMap([
            [$baseFile1, true],
            [$baseDir, false],
            [$baseFile2, true],
            [$baseDictionary, true]
        ]);
        $this->staticDirectoryMock->expects(self::exactly(2))->method('copyFile')->withConsecutive(
            [$baseFile1, $locale . $file1, null],
            [$baseFile2, $locale . $file2, null]
        );

        $this->translationJsConfig->expects(self::exactly(4))->method('getDictionaryFileName')
            ->willReturn($dictionary);

        $this->translationJsConfig->expects($this->once())->method('dictionaryEnabled')->willReturn(true);

        $this->translator->expects($this->once())->method('setLocale')->with($locale);
        $this->translator->expects($this->once())->method('loadData')->with($area, true);

        $this->assetRepo->expects($this->once())->method('createAsset')
            ->with(
                $dictionary,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale]
            )
            ->willReturn($this->asset);

        $this->assetPublisher->expects($this->once())->method('publish');

        $model = $this->getModel([
            DeployInterface::DEPLOY_BASE_LOCALE => $baseLocale,
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
        return (new ObjectManager($this))->getObject(
            LocaleQuickDeploy::class,
            [
                'output' => $this->outputMock,
                'staticDirectory' => $this->staticDirectoryMock,
                'options' => $options,
                'translationJsConfig' => $this->translationJsConfig,
                'translator' => $this->translator,
                'assetRepo' => $this->assetRepo,
                'assetPublisher' => $this->assetPublisher
            ]
        );
    }
}
