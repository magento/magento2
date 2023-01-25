<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Model\Layout\Update;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Model\Layout\Update\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $_objectHelper;

    /**
     * @var DomFactory|MockObject
     */
    private $domConfigFactory;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\Validator|MockObject
     */
    private $model;

    /**
     * @var UrnResolver|MockObject
     */
    private $urnResolver;

    /**
     * @var ValidationStateInterface|MockObject
     */
    private $validationState;

    protected function setUp(): void
    {
        $this->_objectHelper = new ObjectManager($this);
        $this->domConfigFactory = $this->getMockBuilder(
            DomFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->urnResolver = $this->getMockBuilder(
            UrnResolver::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->validationState = $this->getMockBuilder(
            ValidationStateInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->_objectHelper->getObject(
            Validator::class,
            [
                'domConfigFactory' => $this->domConfigFactory,
                'urnResolver' => $this->urnResolver,
                'validationState' => $this->validationState,
            ]
        );
    }

    /**
     * @param string $layoutUpdate
     * @return Validator
     */
    protected function _createValidator($layoutUpdate)
    {
        $params = [
            'xml' => '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
                trim($layoutUpdate) . '</layout>',
            'schemaFile' => $this->urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/page_layout.xsd'),
            'validationState' => $this->validationState,
        ];

        $this->domConfigFactory->expects(
            $this->once()
        )->method(
            'createDom'
        )->with(
            $params
        )->willReturnSelf();

        return $this->model;
    }

    /**
     * @dataProvider testIsValidNotSecurityCheckDataProvider
     * @param string $layoutUpdate
     * @param boolean $expectedResult
     * @param array $messages
     */
    public function testIsValidNotSecurityCheck($layoutUpdate, $expectedResult, $messages)
    {
        $model = $this->_createValidator($layoutUpdate);
        $this->assertEquals(
            $expectedResult,
            $model->isValid(
                $layoutUpdate,
                Validator::LAYOUT_SCHEMA_PAGE_HANDLE,
                false
            )
        );
        $this->assertEquals($messages, $model->getMessages());
    }

    /**
     * @return array
     */
    public function testIsValidNotSecurityCheckDataProvider()
    {
        return [
            ['test', true, []],
        ];
    }

    /**
     * @dataProvider testIsValidSecurityCheckDataProvider
     * @param string $layoutUpdate
     * @param boolean $expectedResult
     * @param array $messages
     */
    public function testIsValidSecurityCheck($layoutUpdate, $expectedResult, $messages)
    {
        $model = $this->_createValidator($layoutUpdate);
        $this->assertEquals(
            $model->isValid(
                $layoutUpdate,
                Validator::LAYOUT_SCHEMA_PAGE_HANDLE,
                true
            ),
            $expectedResult
        );
        $this->assertEquals($model->getMessages(), $messages);
    }

    /**
     * @return array
     */
    public function testIsValidSecurityCheckDataProvider()
    {
        $insecureHelper = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="helper" helper="Helper_Class"/>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        $insecureUpdater = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="string">
                  <updater>Updater_Model</updater>
                  <value>test</value>
              </argument>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        $secureLayout = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="string">test</argument>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        return [
            [
                $insecureHelper,
                false,
                [
                    Validator::HELPER_ARGUMENT_TYPE => 'Helper arguments should not be used in custom layout updates.',
                ],
            ],
            [
                $insecureUpdater,
                false,
                [
                    Validator::UPDATER_MODEL => 'Updater model should not be used in custom layout updates.',
                ],
            ],
            [$secureLayout, true, []],
        ];
    }

    public function testIsValidThrowsValidationException()
    {
        $this->expectException('Magento\Framework\Config\Dom\ValidationException');
        $this->expectExceptionMessage('Please correct the XML data and try again.');
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new ValidationException('Please correct the XML data and try again.')
        );
        $this->model->isValid('test');
    }

    public function testIsValidThrowsValidationSchemaException()
    {
        $this->expectException('Magento\Framework\Config\Dom\ValidationSchemaException');
        $this->expectExceptionMessage('Please correct the XSD data and try again.');
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new ValidationSchemaException(
                new Phrase('Please correct the XSD data and try again.')
            )
        );
        $this->model->isValid('test');
    }

    public function testIsValidThrowsException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Exception.');
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new \Exception('Exception.')
        );
        $this->model->isValid('test');
    }
}
