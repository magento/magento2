<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Variable\Model\ResourceModel\Variable\Collection;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Variable\Model\Variable
     */
    private $model;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceCollectionMock;

    /**
     * @var  \Magento\Framework\Phrase
     */
    private $validationFailedPhrase;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\Variable\Model\ResourceModel\Variable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            \Magento\Variable\Model\Variable::class,
            [
                'escaper' => $this->escaperMock,
                'resource' => $this->resourceMock,
                'resourceCollection' => $this->resourceCollectionMock,
            ]
        );
        $this->validationFailedPhrase = __('Validation has failed.');
    }

    public function testGetValueHtml()
    {
        $type = \Magento\Variable\Model\Variable::TYPE_HTML;
        $html = '<html/>';
        $this->model->setData('html_value', $html);
        $this->assertSame($html, $this->model->getValue($type));
    }

    public function testGetValueEmptyHtml()
    {
        $type = \Magento\Variable\Model\Variable::TYPE_HTML;
        $html = '';
        $plain = 'unescaped_plain_text';
        $escapedPlain = 'escaped_plain_text';
        $this->model->setData('html_value', $html);
        $this->model->setData('plain_value', $plain);
        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($plain)
            ->willReturn($escapedPlain);
        $this->assertSame($escapedPlain, $this->model->getValue($type));
    }

    public function testGetValueText()
    {
        $type = \Magento\Variable\Model\Variable::TYPE_TEXT;
        $plain = 'plain';
        $this->model->setData('plain_value', $plain);
        $this->assertSame($plain, $this->model->getValue($type));
    }

    /**
     * @dataProvider validateMissingInfoDataProvider
     */
    public function testValidateMissingInfo($code, $name)
    {
        $this->model->setCode($code)->setName($name);
        $this->assertEquals($this->validationFailedPhrase, $this->model->validate());
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate($variableArray, $objectId, $expectedResult)
    {
        $code = 'variable_code';
        $this->model->setCode($code)->setName('some_name');
        $this->resourceMock->expects($this->once())
            ->method('getVariableByCode')
            ->with($code)
            ->willReturn($variableArray);
        $this->model->setId($objectId);
        $this->assertEquals($expectedResult, $this->model->validate($variableArray));
    }

    public function testGetVariablesOptionArrayNoGroup()
    {
        $origOptions = [
            ['value' => 'VAL', 'label' => 'LBL'],
        ];

        $transformedOptions = [
            ['value' => '{{customVar code=VAL}}', 'label' => __('%1', 'LBL')],
        ];

        $this->resourceCollectionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn($origOptions);
        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($origOptions[0]['label'])
            ->willReturn($origOptions[0]['label']);
        $this->assertEquals($transformedOptions, $this->model->getVariablesOptionArray());
    }

    public function testGetVariablesOptionArrayWithGroup()
    {
        $origOptions = [
            ['value' => 'VAL', 'label' => 'LBL'],
        ];

        $transformedOptions = [
            [
                'label' => __('Custom Variables'),
                'value' => [
                    ['value' => '{{customVar code=VAL}}', 'label' => __('%1', 'LBL')],
                ],
            ],
        ];

        $this->resourceCollectionMock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn($origOptions);
        $this->escaperMock->expects($this->atLeastOnce())
            ->method('escapeHtml')
            ->with($origOptions[0]['label'])
            ->willReturn($origOptions[0]['label']);
        $this->assertEquals($transformedOptions, $this->model->getVariablesOptionArray(true));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        $variable = [
            'variable_id' => 'matching_id',
        ];
        return [
            'Empty Variable' => [[], null, true],
            'IDs match' => [$variable, 'matching_id', true],
            'IDs do not match' => [$variable, 'non_matching_id', __('Variable Code must be unique.')],
        ];
    }

    /**
     * @return array
     */
    public function validateMissingInfoDataProvider()
    {
        return [
            'Missing code' => ['', 'some-name'],
            'Missing name' => ['some-code', ''],
        ];
    }
}
