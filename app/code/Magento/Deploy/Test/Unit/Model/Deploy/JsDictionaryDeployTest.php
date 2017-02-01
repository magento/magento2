<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Test\Unit\Model\Deploy;

use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Translate\Js\Config as TranslationJsConfig;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\LocalInterface as Asset;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Deploy\Model\Deploy\JsDictionaryDeploy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class JsDictionaryDeployTest extends \PHPUnit_Framework_TestCase
{
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

    /**
     * @var JsDictionaryDeploy
     */
    private $model;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    protected function setUp()
    {
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->setMethods(['writeln', 'isVeryVerbose'])
            ->getMockForAbstractClass();

        $this->translationJsConfig = $this->getMock(TranslationJsConfig::class, [], [], '', false);
        $this->translator = $this->getMockForAbstractClass(TranslateInterface::class, [], '', false, false, true);
        $this->assetRepo = $this->getMock(Repository::class, [], [], '', false);
        $this->asset = $this->getMockForAbstractClass(Asset::class, [], '', false, false, true);
        $this->assetPublisher = $this->getMock(Publisher::class, [], [], '', false);

        $this->model = (new ObjectManager($this))->getObject(
            JsDictionaryDeploy::class,
            [
                'translationJsConfig' => $this->translationJsConfig,
                'translator' => $this->translator,
                'assetRepo' => $this->assetRepo,
                'assetPublisher' => $this->assetPublisher,
                'output' => $this->output
            ]
        );
    }

    public function testDeploy()
    {
        $area = 'adminhtml';
        $themePath = 'Magento/backend';
        $locale = 'uk_UA';

        $dictionary = 'js-translation.json';

        $this->translationJsConfig->expects(self::exactly(1))->method('getDictionaryFileName')
            ->willReturn($dictionary);

        $this->translator->expects($this->once())->method('setLocale')->with($locale);
        $this->translator->expects($this->once())->method('loadData')->with($area, true);

        $this->assetRepo->expects($this->once())->method('createAsset')
            ->with(
                $dictionary,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale]
            )
            ->willReturn($this->asset);

        $this->assetPublisher->expects($this->once())->method('publish');

        $this->model->deploy($area, $themePath, $locale);
    }

}