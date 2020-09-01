<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Helper\Form;
use Magento\GoogleOptimizer\Model\Code;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var Form
     */
    protected $_helper;

    /**
     * @var MockObject
     */
    protected $_formMock;

    /**
     * @var MockObject
     */
    protected $_fieldsetMock;

    /**
     * @var MockObject
     */
    protected $_experimentCodeMock;

    protected function setUp(): void
    {
        $this->_formMock = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->addMethods(['setFieldNameSuffix'])
            ->onlyMethods(['addFieldset'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_fieldsetMock = $this->createMock(Fieldset::class);
        $this->_experimentCodeMock = $this->getMockBuilder(Code::class)
            ->addMethods(['getExperimentScript', 'getCodeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->createMock(Context::class);
        $data = ['context' => $context];
        $objectManagerHelper = new ObjectManager($this);
        $this->_helper = $objectManagerHelper->getObject(Form::class, $data);
    }

    public function testAddFieldsWithExperimentCode()
    {
        $experimentCode = 'some-code';
        $experimentCodeId = 'code-id';
        $this->_experimentCodeMock->expects(
            $this->once()
        )->method(
            'getExperimentScript'
        )->willReturn(
            $experimentCode
        );
        $this->_experimentCodeMock->expects(
            $this->once()
        )->method(
            'getCodeId'
        )->willReturn(
            $experimentCodeId
        );
        $this->_prepareFormMock($experimentCode, $experimentCodeId);

        $this->_helper->addGoogleoptimizerFields($this->_formMock, $this->_experimentCodeMock);
    }

    public function testAddFieldsWithoutExperimentCode()
    {
        $experimentCode = '';
        $experimentCodeId = '';
        $this->_prepareFormMock($experimentCode, $experimentCodeId);

        $this->_helper->addGoogleoptimizerFields($this->_formMock, null);
    }

    /**
     * @param string|array $experimentCode
     * @param string $experimentCodeId
     */
    protected function _prepareFormMock($experimentCode, $experimentCodeId)
    {
        $this->_formMock->expects(
            $this->once()
        )->method(
            'addFieldset'
        )->with(
            'googleoptimizer_fields',
            ['legend' => 'Google Analytics Content Experiments Code']
        )->willReturn(
            $this->_fieldsetMock
        );

        $this->_fieldsetMock->expects(
            $this->at(0)
        )->method(
            'addField'
        )->with(
            'experiment_script',
            'textarea',
            [
                'name' => 'experiment_script',
                'label' => 'Experiment Code',
                'value' => $experimentCode,
                'class' => 'textarea googleoptimizer',
                'required' => false,
                'note' => 'Experiment code should be added to the original page only.',
                'data-form-part' => ''
            ]
        );

        $this->_fieldsetMock->expects(
            $this->at(1)
        )->method(
            'addField'
        )->with(
            'code_id',
            'hidden',
            [
                'name' => 'code_id',
                'value' => $experimentCodeId,
                'required' => false,
                'data-form-part' => ''
            ]
        );
        $this->_formMock->expects($this->once())->method('setFieldNameSuffix')->with('google_experiment');
    }
}
