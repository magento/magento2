<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\Paging;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class ViewTest
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var ConfigBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationBuilderMock;

    /**
     * @var TemplateContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderContextMock;

    /**
     * @var ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationFactoryMock;

    /**
     * @var ContentTypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeFactoryMock;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepoMock;

    public function setUp()
    {
        $this->configurationFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\ConfigFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->renderContextMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\Context',
            ['getNamespace', 'getStorage', 'getRequestParam'],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            ['getAssetRepository'],
            [],
            '',
            false
        );
        $this->contentTypeFactoryMock = $this->getMock('Magento\Ui\ContentType\ContentTypeFactory', [], [], '', false);
        $this->configurationBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface'
        );
        $this->assetRepoMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getAssetRepository')->willReturn($this->assetRepoMock);

        $this->view = new \Magento\Ui\Paging\View(
            $this->contextMock,
            $this->renderContextMock,
            $this->contentTypeFactoryMock,
            $this->configurationFactoryMock,
            $this->configurationBuilderMock
        );
    }

    public function testPrepare()
    {
        $paramsSize = 20;
        $paramsPage = 1;
        $nameSpace = 'namespace';
        $configurationMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigInterface'
        );
        $this->renderContextMock->expects($this->any())->method('getNamespace')->willReturn($nameSpace);
        $this->configurationFactoryMock->expects($this->once())->method('create')->willReturn($configurationMock);

        $storageMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigStorageInterface'
        );
        $dataCollectionMock = $this->getMock('Magento\Framework\Data\Collection', ['setCurPage'], [], '', false);

        $this->renderContextMock->expects($this->any())->method('getStorage')->willReturn($storageMock);
        $storageMock->expects($this->once())
            ->method('addComponentsData')
            ->with($configurationMock)
            ->willReturnSelf();
        $storageMock->expects($this->once())->method('getDataCollection')->willReturn($dataCollectionMock);

        $configurationMock->expects($this->at(1))->method('getData')->with('current')->willReturn($paramsSize);
        $this->renderContextMock->expects($this->any())->method('getRequestParam')->willReturn($paramsPage);
        $configurationMock->expects($this->at(2))->method('getData')->with('pageSize')->willReturn($paramsPage);
        $this->renderContextMock->expects($this->any())->method('getRequestParam')->willReturn($paramsSize);
        $dataCollectionMock->expects($this->any())->method('setCurPage')->with($paramsPage)->willReturnSelf();
        $dataCollectionMock->expects($this->any())->method('setPageSize')->with($paramsSize)->willReturnSelf();

        $this->assertNull($this->view->prepare());
    }
}
