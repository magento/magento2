<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Block\Plugin;

use Magento\CatalogSearch\Block\Plugin\FrontTabPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogSearch\Model\Source\Weight as WeightSource;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front as ProductAttributeFrontTabBlock;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontTabPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var WeightSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $weightSourceMock;

    /**
     * @var ProductAttributeFrontTabBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var Form|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formMock;

    /**
     * @var Fieldset|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldsetMock;

    /**
     * @var AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childElementMock;

    /**
     * @var AbstractBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childBlockMock;

    protected function setUp()
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
