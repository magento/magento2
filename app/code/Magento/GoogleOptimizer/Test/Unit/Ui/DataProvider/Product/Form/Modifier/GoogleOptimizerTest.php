<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\GoogleOptimizer\Helper\Code;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\GoogleOptimizer\Ui\DataProvider\Product\Form\Modifier\GoogleOptimizer;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GoogleOptimizerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var Data|MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code|MockObject
     */
    protected $codeHelperMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var GoogleOptimizer
     */
    protected $googleOptimizer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->productMock = $this->createMock(Product::class);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->dataHelperMock = $this->createMock(Data::class);
        $this->codeHelperMock = $this->createMock(Code::class);

        $this->googleOptimizer = $this->objectManagerHelper->getObject(
            GoogleOptimizer::class,
            [
                'locator' => $this->locatorMock,
                'dataHelper' => $this->dataHelperMock,
                'codeHelper' => $this->codeHelperMock
            ]
        );
    }

    /**
     * @param bool $flag
     * @return void
     */
    protected function canShowPanel($flag)
    {
        $storeId = 1;
        $this->productMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->dataHelperMock->expects($this->once())
            ->method('isGoogleExperimentActive')
            ->with($storeId)
            ->willReturn($flag);
    }

    /**
     * @return void
     */
    public function testGetDataGoogleExperimentDisabled()
    {
        $this->canShowPanel(false);
        $this->assertEquals([], $this->googleOptimizer->modifyData([]));
    }

    /**
     * @param int|null $productId
     * @param string $experimentScript
     * @param string $codeId
     * @param int $expectedCalls
     * @return void
     * @dataProvider getDataGoogleExperimentEnabledDataProvider
     */
    public function testGetDataGoogleExperimentEnabled($productId, $experimentScript, $codeId, $expectedCalls)
    {
        $expectedResult[$productId]['google_experiment'] = [
            'experiment_script' => $experimentScript,
            'code_id' => $codeId,
        ];

        $this->canShowPanel(true);

        /** @var \Magento\GoogleOptimizer\Model\Code|MockObject $codeModelMock */
        $codeModelMock = $this->getMockBuilder(\Magento\GoogleOptimizer\Model\Code::class)
            ->addMethods(['getExperimentScript', 'getCodeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $codeModelMock->expects($this->exactly($expectedCalls))
            ->method('getExperimentScript')
            ->willReturn($experimentScript);
        $codeModelMock->expects($this->exactly($expectedCalls))
            ->method('getCodeId')
            ->willReturn($codeId);

        $this->codeHelperMock->expects($this->exactly($expectedCalls))
            ->method('getCodeObjectByEntity')
            ->with($this->productMock)
            ->willReturn($codeModelMock);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);

        $this->assertEquals($expectedResult, $this->googleOptimizer->modifyData([]));
    }

    /**
     * @return array
     */
    public static function getDataGoogleExperimentEnabledDataProvider()
    {
        return [
            ['productId' => 2, 'experimentScript' => 'some script', 'codeId' => '3', 'expectedCalls' => 1],
            ['productId' => null, 'experimentScript' => '', 'codeId' => '', 'expectedCalls' => 0],
        ];
    }

    /**
     * @return void
     */
    public function testGetMetaGoogleExperimentDisabled()
    {
        $this->canShowPanel(false);
        $this->assertEquals([], $this->googleOptimizer->modifyMeta([]));
    }

    /**
     * @return void
     */
    public function testGetMetaGoogleExperimentEnabled()
    {
        $expectedResult[GoogleOptimizer::GROUP_CODE] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Fieldset::NAME,
                        'label' => __('Product View Optimization'),
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => 100,
                        'dataScope' => 'data.google_experiment',
                    ],
                ],
            ],
            'children' => [
                'experiment_script' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Textarea::NAME,
                                'dataType' => Text::NAME,
                                'label' => __('Experiment Code'),
                                'notice' => __('Experiment code should be added to the original page only.'),
                                'dataScope' => 'experiment_script',
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                ],
                'code_id' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Input::NAME,
                                'dataType' => Text::NAME,
                                'visible' => false,
                                'label' => '',
                                'dataScope' => 'code_id',
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->canShowPanel(true);
        $this->assertEquals($expectedResult, $this->googleOptimizer->modifyMeta([]));
    }
}
