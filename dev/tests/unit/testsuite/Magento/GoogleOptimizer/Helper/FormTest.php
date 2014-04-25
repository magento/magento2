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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Helper;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Form
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldsetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_experimentCodeMock;

    protected function setUp()
    {
        $this->_formMock = $this->getMock(
            'Magento\Framework\Data\Form',
            array('setFieldNameSuffix', 'addFieldset'),
            array(),
            '',
            false
        );
        $this->_fieldsetMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Fieldset',
            array(),
            array(),
            '',
            false
        );
        $this->_experimentCodeMock = $this->getMock(
            'Magento\GoogleOptimizer\Model\Code',
            array('getExperimentScript', 'getCodeId', '__wakeup'),
            array(),
            '',
            false
        );
        $context = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);
        $data = array('context' => $context);
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManagerHelper->getObject('Magento\GoogleOptimizer\Helper\Form', $data);
    }

    public function testAddFieldsWithExperimentCode()
    {
        $experimentCode = 'some-code';
        $experimentCodeId = 'code-id';
        $this->_experimentCodeMock->expects(
            $this->once()
        )->method(
            'getExperimentScript'
        )->will(
            $this->returnValue($experimentCode)
        );
        $this->_experimentCodeMock->expects(
            $this->once()
        )->method(
            'getCodeId'
        )->will(
            $this->returnValue($experimentCodeId)
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
            array('legend' => 'Google Analytics Content Experiments Code')
        )->will(
            $this->returnValue($this->_fieldsetMock)
        );

        $this->_fieldsetMock->expects(
            $this->at(0)
        )->method(
            'addField'
        )->with(
            'experiment_script',
            'textarea',
            array(
                'name' => 'experiment_script',
                'label' => 'Experiment Code',
                'value' => $experimentCode,
                'class' => 'textarea googleoptimizer',
                'required' => false,
                'note' => 'Note: Experiment code should be added to the original page only.'
            )
        );

        $this->_fieldsetMock->expects(
            $this->at(1)
        )->method(
            'addField'
        )->with(
            'code_id',
            'hidden',
            array('name' => 'code_id', 'value' => $experimentCodeId, 'required' => false)
        );
        $this->_formMock->expects($this->once())->method('setFieldNameSuffix')->with('google_experiment');
    }
}
