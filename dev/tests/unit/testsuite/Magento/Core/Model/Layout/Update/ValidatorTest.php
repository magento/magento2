<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Layout\Update;

use Magento\Core\Model\Layout\Update\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    public function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param string $layoutUpdate
     * @param boolean $isSchemaValid
     * @return Validator
     */
    protected function _createValidator($layoutUpdate, $isSchemaValid = true)
    {
        $dirList = $this->getMockBuilder('Magento\Framework\App\Filesystem\DirectoryList')
            ->disableOriginalConstructor()
            ->getMock();
        $dirList->expects(
            $this->exactly(2)
        )->method(
            'getPath'
        )->will(
            $this->returnValue('dummyDir')
        );

        $domConfigFactory = $this->getMockBuilder(
            'Magento\Framework\Config\DomFactory'
        )->disableOriginalConstructor()->getMock();

        $params = [
            'xml' => '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . trim(
                $layoutUpdate
            ) . '</layout>',
            'schemaFile' => 'dummyDir/Magento/Framework/View/Layout/etc/page_layout.xsd',
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

        $model = $this->_objectHelper->getObject(
            'Magento\Core\Model\Layout\Update\Validator',
            ['dirList' => $dirList, 'domConfigFactory' => $domConfigFactory]
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
        $this->assertEquals($model->getMessages(), $messages);
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
