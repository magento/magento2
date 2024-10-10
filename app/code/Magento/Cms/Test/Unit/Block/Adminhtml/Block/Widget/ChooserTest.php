<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block\Adminhtml\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Block\Adminhtml\Block\Widget\Chooser;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cms\Block\Adminhtml\Block\Widget\Chooser
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends TestCase
{
    /**
     * @var Chooser
     */
    protected $this;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var Random|MockObject
     */
    protected $mathRandomMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var BlockFactory|MockObject
     */
    protected $blockFactoryMock;

    /**
     * @var AbstractElement|MockObject
     */
    protected $elementMock;

    /**
     * @var Block|MockObject
     */
    protected $modelBlockMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $chooserMock;

    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'escapeHtml',
                ]
            )
            ->getMock();
        $this->blockFactoryMock = $this->getMockBuilder(BlockFactory::class)
            ->onlyMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementMock = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->addMethods(['getValue'])
            ->onlyMethods(
                [
                    'getId',
                    'setData',
                ]
            )
            ->getMock();
        $this->modelBlockMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getTitle',
                    'load',
                    'getId',
                ]
            )
            ->getMock();
        $this->chooserMock = $this->getMockBuilder(BlockInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setElement',
                    'setConfig',
                    'setFieldsetId',
                    'setSourceUrl',
                    'setUniqId',
                    'setLabel'
                ]
            )
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'layout'     => $this->layoutMock,
                'mathRandom' => $this->mathRandomMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper'    => $this->escaper,
            ]
        );
        $this->this = $objectManager->getObject(
            Chooser::class,
            [
                'context'      => $this->context,
                'blockFactory' => $this->blockFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Block\Widget\Chooser::prepareElementHtml
     * @param string $elementValue
     * @param integer|null $modelBlockId
     *
     * @dataProvider prepareElementHtmlDataProvider
     */
    public function testPrepareElementHtml($elementValue, $modelBlockId)
    {
        $elementId    = 1;
        $uniqId       = '126hj4h3j73hk7b347jhkl37gb34';
        $sourceUrl    = 'cms/block_widget/chooser/126hj4h3j73hk7b347jhkl37gb34';
        $config       = ['key1' => 'value1'];
        $fieldsetId   = 2;
        $html         = 'some html';
        $title        = 'some "><img src=y onerror=prompt(document.domain)>; title';
        $titleEscaped = 'some &quot;&gt;&lt;img src=y onerror=prompt(document.domain)&gt;; title';

        $this->this->setConfig($config);
        $this->this->setFieldsetId($fieldsetId);

        $this->elementMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($elementId);
        $this->mathRandomMock->expects($this->atLeastOnce())
            ->method('getUniqueHash')
            ->with($elementId)
            ->willReturn($uniqId);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('cms/block_widget/chooser', ['uniq_id' => $uniqId])
            ->willReturn($sourceUrl);
        $this->layoutMock->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with(\Magento\Widget\Block\Adminhtml\Widget\Chooser::class)
            ->willReturn($this->chooserMock);
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setElement')
            ->with($this->elementMock)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setConfig')
            ->with($config)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setFieldsetId')
            ->with($fieldsetId)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setSourceUrl')
            ->with($sourceUrl)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setUniqId')
            ->with($uniqId)
            ->willReturnSelf();
        $this->elementMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($elementValue);
        $this->blockFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->modelBlockMock);
        $this->modelBlockMock->expects($this->any())
            ->method('load')
            ->with($elementValue)
            ->willReturnSelf();
        $this->modelBlockMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelBlockId);
        $this->modelBlockMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('toHtml')
            ->willReturn($html);
        if (!empty($elementValue) && !empty($modelBlockId)) {
            $this->escaper->expects(($this->atLeastOnce()))
                ->method('escapeHtml')
                ->willReturn($titleEscaped);
            $this->chooserMock->expects($this->atLeastOnce())
                ->method('setLabel')
                ->with($titleEscaped)
                ->willReturnSelf();
        }
        $this->elementMock->expects($this->atLeastOnce())
            ->method('setData')
            ->with('after_element_html', $html)
            ->willReturnSelf();

        $this->assertEquals($this->elementMock, $this->this->prepareElementHtml($this->elementMock));
    }

    /**
     * @return array
     */
    public static function prepareElementHtmlDataProvider()
    {
        return [
            'elementValue NOT EMPTY, modelBlockId NOT EMPTY' => [
                'elementValue' => 'some value',
                'modelBlockId' => 1,
            ],
            'elementValue NOT EMPTY, modelBlockId IS EMPTY' => [
                'elementValue' => 'some value',
                'modelBlockId' => null,
            ],
            'elementValue IS EMPTY, modelBlockId NEVER REACHED' => [
                'elementValue' => '',
                'modelBlockId' => 1,
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Block\Widget\Chooser::getGridUrl
     */
    public function testGetGridUrl()
    {
        $url = 'some url';

        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('cms/block_widget/chooser', ['_current' => true])
            ->willReturn($url);

        $this->assertEquals($url, $this->this->getGridUrl());
    }
}
