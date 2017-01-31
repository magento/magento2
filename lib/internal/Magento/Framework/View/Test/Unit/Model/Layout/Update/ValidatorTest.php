<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Model\Layout\Update;

use \Magento\Framework\View\Model\Layout\Update\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param string $layoutUpdate
     * @param boolean $isSchemaValid
     * @return Validator
     */
    protected function _createValidator($layoutUpdate, $isSchemaValid = true)
    {
        $domConfigFactory = $this->getMockBuilder(
            'Magento\Framework\Config\DomFactory'
        )->disableOriginalConstructor()->getMock();

        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $params = [
            'xml' => '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
                trim($layoutUpdate) . '</layout>',
            'schemaFile' => $urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/page_layout.xsd'),
        ];

        $exceptionMessage = 'validation exception';
        $domConfigFactory->expects(
            $this->once()
        )->method(
            'createDom'
        )->with(
            $this->equalTo($params)
        )->will(
            $isSchemaValid ? $this->returnSelf() : $this->throwException(
                new \Magento\Framework\Config\Dom\ValidationException($exceptionMessage)
            )
        );
        $urnResolver = $this->_objectHelper->getObject('Magento\Framework\Config\Dom\UrnResolver');
        $model = $this->_objectHelper->getObject(
            'Magento\Framework\View\Model\Layout\Update\Validator',
            ['domConfigFactory' => $domConfigFactory, 'urnResolver' => $urnResolver]
        );

        return $model;
    }

    /**
     * @dataProvider testIsValidNotSecurityCheckDataProvider
     * @param string $layoutUpdate
     * @param boolean $isValid
     * @param boolean $expectedResult
     * @param array $messages
     */
    public function testIsValidNotSecurityCheck($layoutUpdate, $isValid, $expectedResult, $messages)
    {
        $model = $this->_createValidator($layoutUpdate, $isValid);
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
            ['test', true, true, []],
            [
                'test',
                false,
                false,
                [
                    Validator::XML_INVALID => 'Please correct the XML data and try again. validation exception'
                ]
            ]
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
                    Validator::HELPER_ARGUMENT_TYPE => 'Helper arguments should not be used in custom layout updates.'
                ],
            ],
            [
                $insecureUpdater,
                false,
                [
                    Validator::UPDATER_MODEL => 'Updater model should not be used in custom layout updates.'
                ]
            ],
            [$secureLayout, true, []]
        ];
    }
}
