<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Model\Layout\Update;

use Magento\Framework\Phrase;
use Magento\Framework\View\Model\Layout\Update\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectHelper;

    /**
     * @var \Magento\Framework\Config\DomFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $domConfigFactory;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urnResolver;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->domConfigFactory = $this->getMockBuilder(
            \Magento\Framework\Config\DomFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->urnResolver = $this->getMockBuilder(
            \Magento\Framework\Config\Dom\UrnResolver::class
        )->disableOriginalConstructor()->getMock();

        $this->model = $this->_objectHelper->getObject(
            \Magento\Framework\View\Model\Layout\Update\Validator::class,
            ['domConfigFactory' => $this->domConfigFactory, 'urnResolver' => $this->urnResolver]
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
        ];

        $this->domConfigFactory->expects(
            $this->once()
        )->method(
            'createDom'
        )->with(
            $this->equalTo($params)
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

    /**
     * @expectedException \Magento\Framework\Config\Dom\ValidationException
     * @expectedExceptionMessage Please correct the XML data and try again.
     */
    public function testIsValidThrowsValidationException()
    {
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new \Magento\Framework\Config\Dom\ValidationException('Please correct the XML data and try again.')
        );
        $this->model->isValid('test');
    }

    /**
     * @expectedException \Magento\Framework\Config\Dom\ValidationSchemaException
     * @expectedExceptionMessage Please correct the XSD data and try again.
     */
    public function testIsValidThrowsValidationSchemaException()
    {
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new \Magento\Framework\Config\Dom\ValidationSchemaException(
                new Phrase('Please correct the XSD data and try again.')
            )
        );
        $this->model->isValid('test');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Exception.
     */
    public function testIsValidThrowsException()
    {
        $this->domConfigFactory->expects($this->once())->method('createDom')->willThrowException(
            new \Exception('Exception.')
        );
        $this->model->isValid('test');
    }
}
