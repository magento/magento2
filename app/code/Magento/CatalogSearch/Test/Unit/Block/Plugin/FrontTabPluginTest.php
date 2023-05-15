<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Block\Plugin;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front as ProductAttributeFrontTabBlock;
use Magento\CatalogSearch\Block\Plugin\FrontTabPlugin;
use Magento\CatalogSearch\Model\Source\Weight as WeightSource;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\AbstractBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontTabPluginTest extends TestCase
{
    /**
     * @var FrontTabPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var WeightSource|MockObject
     */
    private $weightSourceMock;

    /**
     * @var ProductAttributeFrontTabBlock|MockObject
     */
    private $subjectMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    /**
     * @var Fieldset|MockObject
     */
    private $fieldsetMock;

    /**
     * @var AbstractElement|MockObject
     */
    private $childElementMock;

    /**
     * @var AbstractBlock|MockObject
     */
    private $childBlockMock;

    protected function setUp(): void
    {
        $this->weightSourceMock = $this->getMockBuilder(WeightSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ProductAttributeFrontTabBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldsetMock = $this->getMockBuilder(Fieldset::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childElementMock = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->childBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldMap', 'addFieldDependence'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            FrontTabPlugin::class,
            ['weightSource' => $this->weightSourceMock]
        );
    }

    public function testBeforeSetForm()
    {
        $weightOptions = [1 => '1', 2 => '2'];

        $this->formMock->expects(static::any())
            ->method('getElement')
            ->with('front_fieldset')
            ->willReturn($this->fieldsetMock);
        $this->weightSourceMock->expects(static::any())
            ->method('getOptions')
            ->willReturn($weightOptions);
        $this->fieldsetMock->expects(static::once())
            ->method('addField')
            ->with(
                'search_weight',
                'select',
                [
                    'name' => 'search_weight',
                    'label' => __('Search Weight'),
                    'note' => __('10 is the highest priority/heaviest weighting.'),
                    'values' => $weightOptions
                ],
                'is_searchable',
                false
            )
            ->willReturn($this->childElementMock);
        $this->subjectMock->expects(static::any())
            ->method('getChildBlock')
            ->with('form_after')
            ->willReturn($this->childBlockMock);
        $this->childBlockMock->expects(static::once())
            ->method('addFieldMap')
            ->with('search_weight', 'search_weight')
            ->willReturnSelf();
        $this->childBlockMock->expects(static::once())
            ->method('addFieldDependence')
            ->with('search_weight', 'searchable', '1')
            ->willReturnSelf();

        $this->plugin->beforeSetForm($this->subjectMock, $this->formMock);
    }
}
