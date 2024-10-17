<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model;

use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Listener\ReplaceObjectManager\TestProvidesServiceInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use Magento\Variable\Model\ResourceModel\Variable;
use Magento\Variable\Model\ResourceModel\Variable\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{
    /**
     * @var  \Magento\Variable\Model\Variable
     */
    private $model;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable|MockObject
     */
    private $resourceMock;

    /**
     * @var Collection|MockObject
     */
    private $resourceCollectionMock;

    /**
     * @var  Phrase
     */
    private $validationFailedPhrase;

    /**
     * @var  ObjectManager
     */
    private $objectManager;

    /**
     * @var WYSIWYGValidatorInterface
     */
    private $wysiwygValidator;

    protected function setUp(): void
    {
        $this->wysiwygValidator = $this->createMock(WYSIWYGValidatorInterface::class);
        $this->objectManager = new ObjectManager($this);
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(Variable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getServicesForObjMap();
        $this->model = $this->objectManager->getObject(
            \Magento\Variable\Model\Variable::class,
            [
                'escaper' => $this->escaperMock,
                'resource' => $this->resourceMock,
                'resourceCollection' => $this->resourceCollectionMock,
                'wysiwygValidator' => $this->wysiwygValidator
            ]
        );
        $this->validationFailedPhrase = __('Validation has failed.');
    }

    /**
     * Replace Object Manager/Object Mapping
     * @return void
     */
    public function getServicesForObjMap()
    {
        $value = $this->resourceCollectionMock;
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $objectManagerMock->method('create')->willReturnCallback(function () use ($value){
            return $value;
        });
        $objectManagerMock->method('get')->willReturnCallback(function () use ($value){
            return $value;
        });

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
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
    public static function validateDataProvider()
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
    public static function validateMissingInfoDataProvider()
    {
        return [
            'Missing code' => ['', 'some-name'],
            'Missing name' => ['some-code', ''],
        ];
    }

    /**
     * Test Variable validation.
     *
     * @param string $value
     * @param bool $isChanged
     * @param bool $isValidated
     * @param bool $exceptionThrown
     * @dataProvider getWysiwygValidationCases
     */
    public function testBeforeSave(string $value, bool $isChanged, bool $isValidated, bool $exceptionThrown): void
    {
        $actuallyThrown = false;

        if (!$isValidated) {
            $this->wysiwygValidator->expects($this->any())
                ->method('validate')
                ->willThrowException(new ValidationException(__('HTML is invalid')));
        } else {
            $this->wysiwygValidator->expects($this->any())->method('validate');
        }

        $this->model->setData('html_value', $value);

        if (!$isChanged) {
            $this->model->setOrigData('html_value', $value);
        } else {
            $this->model->setOrigData('html_value', $value . '-OLD');
        }

        try {
            $this->model->beforeSave();
        } catch (\Throwable $exception) {
            $actuallyThrown = true;
        }

        $this->assertEquals($exceptionThrown, $actuallyThrown);
    }

    /**
     * Validation cases.
     *
     * @return array
     */
    public static function getWysiwygValidationCases(): array
    {
        return [
            'changed-html-value-without-exception' => ['<b>Test Html</b>',true,true,false],
            'changed-html-value-with-exception' => ['<b>Test Html</b>',true,false,true],
            'no-changed-html-value-without-exception' => ['<b>Test Html</b>',false,false,false],
            'no-html-value-with-exception' => ['',true,false,false]
        ];
    }
}
