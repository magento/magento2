<?php
/**
 * \Magento\Backend\Model\Config\Structure\Element\Field
 *
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
namespace Magento\Backend\Model\Config\Structure\Element;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    const FIELD_TEST_CONSTANT = "field test constant";

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Field
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sourceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_commentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_blockFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_depMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    protected function setUp()
    {
        $this->_iteratorMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Iterator',
            array(),
            array(),
            '',
            false
        );
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_backendFactoryMock = $this->getMock(
            'Magento\Backend\Model\Config\BackendFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_sourceFactoryMock = $this->getMock(
            'Magento\Backend\Model\Config\SourceFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_commentFactoryMock = $this->getMock(
            'Magento\Backend\Model\Config\CommentFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_blockFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\BlockFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_depMapperMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\Field(
            $this->_storeManagerMock,
            $this->_backendFactoryMock,
            $this->_sourceFactoryMock,
            $this->_commentFactoryMock,
            $this->_blockFactoryMock,
            $this->_depMapperMock
        );
    }

    protected function tearDown()
    {
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
        unset($this->_backendFactoryMock);
        unset($this->_sourceFactoryMock);
        unset($this->_commentFactoryMock);
        unset($this->_depMapperMock);
        unset($this->_model);
        unset($this->_blockFactoryMock);
    }

    public function testGetLabelTranslatesLabelAndPrefix()
    {
        $this->_model->setData(array('label' => 'element label'), 'scope');
        $this->assertEquals(__('some prefix') . ' ' . __('element label'), $this->_model->getLabel('some prefix'));
    }

    public function testGetHintTranslatesElementHint()
    {
        $this->_model->setData(array('hint' => 'element hint'), 'scope');
        $this->assertEquals(__('element hint'), $this->_model->getHint());
    }

    public function testGetCommentTranslatesCommentTextIfNoCommentModelIsProvided()
    {
        $this->_model->setData(array('comment' => 'element comment'), 'scope');
        $this->assertEquals(__('element comment'), $this->_model->getComment());
    }

    public function testGetCommentRetrievesCommentFromCommentModelIfItsProvided()
    {
        $config = array('comment' => array('model' => 'Model_Name'));
        $this->_model->setData($config, 'scope');
        $commentModelMock = $this->getMock('Magento\Backend\Model\Config\CommentInterface');
        $commentModelMock->expects(
            $this->once()
        )->method(
            'getCommentText'
        )->with(
            'currentValue'
        )->will(
            $this->returnValue('translatedValue')
        );
        $this->_commentFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Model_Name'
        )->will(
            $this->returnValue($commentModelMock)
        );
        $this->assertEquals('translatedValue', $this->_model->getComment('currentValue'));
    }

    public function testGetTooltipRetunrsTranslatedAttributeIfNoBlockIsProvided()
    {
        $this->_model->setData(array('tooltip' => 'element tooltip'), 'scope');
        $this->assertEquals(__('element tooltip'), $this->_model->getTooltip());
    }

    public function testGetTooltipCreatesTooltipBlock()
    {
        $this->_model->setData(array('tooltip_block' => 'Magento\Core\Block\Tooltip'), 'scope');
        $tooltipBlock = $this->getMock('Magento\Framework\View\Element\BlockInterface');
        $tooltipBlock->expects($this->once())->method('toHtml')->will($this->returnValue('tooltip block'));
        $this->_blockFactoryMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'Magento\Core\Block\Tooltip'
        )->will(
            $this->returnValue($tooltipBlock)
        );
        $this->assertEquals('tooltip block', $this->_model->getTooltip());
    }

    public function testGetTypeReturnsTextByDefault()
    {
        $this->assertEquals('text', $this->_model->getType());
    }

    public function testGetTypeReturnsProvidedType()
    {
        $this->_model->setData(array('type' => 'some_type'), 'scope');
        $this->assertEquals('some_type', $this->_model->getType());
    }

    public function testGetFrontendClass()
    {
        $this->assertEquals('', $this->_model->getFrontendClass());
        $this->_model->setData(array('frontend_class' => 'some class'), 'scope');
        $this->assertEquals('some class', $this->_model->getFrontendClass());
    }

    public function testHasBackendModel()
    {
        $this->assertFalse($this->_model->hasBackendModel());
        $this->_model->setData(array('backend_model' => 'some_model'), 'scope');
        $this->assertTrue($this->_model->hasBackendModel());
    }

    public function testGetBackendModelCreatesBackendModel()
    {
        $this->_backendFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\Model\Name'
        )->will(
            $this->returnValue('backend_model_object')
        );
        $this->_model->setData(array('backend_model' => 'Magento\Framework\Model\Name'), 'scope');
        $this->assertEquals('backend_model_object', $this->_model->getBackendModel());
    }

    public function testGetSectionId()
    {
        $this->_model->setData(array('id' => 'fieldId', 'path' => 'sectionId/groupId/subgroupId'), 'scope');
        $this->assertEquals('sectionId', $this->_model->getSectionId());
    }

    public function testGetGroupPath()
    {
        $this->_model->setData(array('id' => 'fieldId', 'path' => 'sectionId/groupId/subgroupId'), 'scope');
        $this->assertEquals('sectionId/groupId/subgroupId', $this->_model->getGroupPath());
    }

    public function testGetConfigPath()
    {
        $this->_model->setData(array('config_path' => 'custom_config_path'), 'scope');
        $this->assertEquals('custom_config_path', $this->_model->getConfigPath());
    }

    public function testShowInDefault()
    {
        $this->assertFalse($this->_model->showInDefault());
        $this->_model->setData(array('showInDefault' => 1), 'scope');
        $this->assertTrue($this->_model->showInDefault());
    }

    public function testShowInWebsite()
    {
        $this->assertFalse($this->_model->showInWebsite());
        $this->_model->setData(array('showInWebsite' => 1), 'scope');
        $this->assertTrue($this->_model->showInWebsite());
    }

    public function testShowInStore()
    {
        $this->assertFalse($this->_model->showInStore());
        $this->_model->setData(array('showInStore' => 1), 'scope');
        $this->assertTrue($this->_model->showInStore());
    }

    public function testPopulateInput()
    {
        $params = array(
            'type' => 'multiselect',
            'can_be_empty' => true,
            'source_model' => 'some_model',
            'someArr' => array('testVar' => 'testVal')
        );
        $this->_model->setData($params, 'scope');
        $elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Text',
            array('setOriginalData'),
            array(),
            '',
            false
        );
        unset($params['someArr']);
        $elementMock->expects($this->once())->method('setOriginalData')->with($params);
        $this->_model->populateInput($elementMock);
    }

    public function testHasValidation()
    {
        $this->assertFalse($this->_model->hasValidation());
        $this->_model->setData(array('validate' => 'validation class'), 'scope');
        $this->assertTrue($this->_model->hasValidation());
    }

    public function testCanBeEmpty()
    {
        $this->assertFalse($this->_model->canBeEmpty());
        $this->_model->setData(array('can_be_empty' => true), 'scope');
        $this->assertTrue($this->_model->canBeEmpty());
    }

    public function testHasSourceModel()
    {
        $this->assertFalse($this->_model->hasSourceModel());
        $this->_model->setData(array('source_model' => 'some_model'), 'scope');
        $this->assertTrue($this->_model->hasSourceModel());
    }

    public function testHasOptionsWithSourceModel()
    {
        $this->assertFalse($this->_model->hasOptions());
        $this->_model->setData(array('source_model' => 'some_model'), 'scope');
        $this->assertTrue($this->_model->hasOptions());
    }

    public function testHasOptionsWithOptions()
    {
        $this->assertFalse($this->_model->hasOptions());
        $this->_model->setData(array('options' => 'some_option'), 'scope');
        $this->assertTrue($this->_model->hasOptions());
    }

    public function testGetOptionsWithOptions()
    {
        $option = array(array('label' => 'test', 'value' => 0), array('label' => 'test2', 'value' => 1));
        $expected = array(array('label' => __('test'), 'value' => 0), array('label' => __('test2'), 'value' => 1));
        $this->_model->setData(array('options' => array('option' => $option)), 'scope');
        $this->assertEquals($expected, $this->_model->getOptions());
    }

    public function testGetOptionsWithConstantValOptions()
    {
        $option = array(
            array(
                'label' => 'test',
                'value' => "{{\Magento\Backend\Model\Config\Structure\Element\FieldTest::FIELD_TEST_CONSTANT}}"
            )
        );
        $expected = array(
            array(
                'label' => __('test'),
                'value' => \Magento\Backend\Model\Config\Structure\Element\FieldTest::FIELD_TEST_CONSTANT
            )
        );

        $this->_model->setData(array('options' => array('option' => $option)), 'scope');
        $this->assertEquals($expected, $this->_model->getOptions());
    }

    public function testGetOptionsUsesOptionsInterfaceIfNoMethodIsProvided()
    {
        $this->_model->setData(array('source_model' => 'Source_Model_Name'), 'scope');
        $sourceModelMock = $this->getMock('Magento\Framework\Option\ArrayInterface');
        $this->_sourceFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Source_Model_Name'
        )->will(
            $this->returnValue($sourceModelMock)
        );
        $expected = array(array('label' => 'test', 'value' => 0), array('label' => 'test2', 'value' => 1));
        $sourceModelMock->expects(
            $this->once()
        )->method(
            'toOptionArray'
        )->with(
            false
        )->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->_model->getOptions());
    }

    public function testGetOptionsUsesProvidedMethodOfSourceModel()
    {
        $this->_model->setData(
            array('source_model' => 'Source_Model_Name::retrieveElements', 'path' => 'path', 'type' => 'multiselect'),
            'scope'
        );
        $sourceModelMock = $this->getMock('Magento\Framework\Object', array('setPath', 'retrieveElements'));
        $this->_sourceFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Source_Model_Name'
        )->will(
            $this->returnValue($sourceModelMock)
        );
        $expected = array('testVar1' => 'testVal1', 'testVar2' => array('subvar1' => 'subval1'));
        $sourceModelMock->expects($this->once())->method('setPath')->with('path/');
        $sourceModelMock->expects($this->once())->method('retrieveElements')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->_model->getOptions());
    }

    public function testGetOptionsParsesResultOfProvidedMethodOfSourceModelIfTypeIsNotMultiselect()
    {
        $this->_model->setData(
            array('source_model' => 'Source_Model_Name::retrieveElements', 'path' => 'path', 'type' => 'select'),
            'scope'
        );
        $sourceModelMock = $this->getMock('Magento\Framework\Object', array('setPath', 'retrieveElements'));
        $this->_sourceFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Source_Model_Name'
        )->will(
            $this->returnValue($sourceModelMock)
        );
        $sourceModelMock->expects($this->once())->method('setPath')->with('path/');
        $sourceModelMock->expects(
            $this->once()
        )->method(
            'retrieveElements'
        )->will(
            $this->returnValue(array('var1' => 'val1', 'var2' => array('subvar1' => 'subval1')))
        );
        $expected = array(array('label' => 'val1', 'value' => 'var1'), array('subvar1' => 'subval1'));
        $this->assertEquals($expected, $this->_model->getOptions());
    }

    public function testGetDependenciesWithoutDependencies()
    {
        $this->_depMapperMock->expects($this->never())->method('getDependencies');
    }

    public function testGetDependenciesWithDependencies()
    {
        $fields = array(
            'field_4' => array(
                'id' => 'section_2/group_3/field_4',
                'value' => 'someValue',
                'dependPath' => array('section_2', 'group_3', 'field_4')
            ),
            'field_1' => array(
                'id' => 'section_1/group_3/field_1',
                'value' => 'someValue',
                'dependPath' => array('section_1', 'group_3', 'field_1')
            )
        );
        $this->_model->setData(array('depends' => array('fields' => $fields)), 0);
        $this->_depMapperMock->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->with(
            $fields,
            'test_scope',
            'test_prefix'
        )->will(
            $this->returnArgument(0)
        );

        $this->assertEquals($fields, $this->_model->getDependencies('test_prefix', 'test_scope'));
    }

    public function testIsAdvanced()
    {
        $this->_model->setData(array(), 'scope');
        $this->assertFalse($this->_model->isAdvanced());

        $this->_model->setData(array('advanced' => true), 'scope');
        $this->assertTrue($this->_model->isAdvanced());

        $this->_model->setData(array('advanced' => false), 'scope');
        $this->assertFalse($this->_model->isAdvanced());
    }

    public function testGetValidation()
    {
        $this->_model->setData(array(), 'scope');
        $this->assertNull($this->_model->getValidation());

        $this->_model->setData(array('validate' => 'validate'), 'scope');
        $this->assertEquals('validate', $this->_model->getValidation());
    }
}
