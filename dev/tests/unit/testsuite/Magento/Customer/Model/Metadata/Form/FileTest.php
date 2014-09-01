<?php
/**
 * Magento\Customer\Model\Metadata\Form\File
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
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;

class FileTest extends AbstractFormTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\Data */
    protected $coreDataMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\File\Validator\NotProtectedExtension */
    protected $fileValidatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Filesystem */
    protected $fileSystemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    protected function setUp()
    {
        parent::setUp();
        $this->coreDataMock = $this->getMockBuilder(
            'Magento\Core\Helper\Data'
        )->disableOriginalConstructor()->getMock();
        $this->fileValidatorMock = $this->getMockBuilder(
            'Magento\Core\Model\File\Validator\NotProtectedExtension'
        )->disableOriginalConstructor()->getMock();
        $this->fileSystemMock = $this->getMockBuilder(
            'Magento\Framework\App\Filesystem'
        )->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder(
            'Magento\Framework\App\RequestInterface'
        )->disableOriginalConstructor()->setMethods(
            ['getParam', 'getParams', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getCookie']
        )->getMock();
    }

    /**
     * @param array|bool $expected
     * @param string $attributeCode
     * @param bool $isAjax
     * @param string $delete
     * @dataProvider extractValueNoRequestScopeDataProvider
     */
    public function testExtractValueNoRequestScope($expected, $attributeCode = '', $isAjax = false, $delete = '')
    {
        $value = 'value';
        $fileForm = $this->getClass($value, $isAjax);

        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValue(['delete' => $delete])
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue($attributeCode)
        );
        if (!empty($attributeCode)) {
            $_FILES[$attributeCode] = array('attributeCodeValue');
        }
        $this->assertEquals($expected, $fileForm->extractValue($this->requestMock));
        if (!empty($attributeCode)) {
            unset($_FILES[$attributeCode]);
        }
    }

    public function extractValueNoRequestScopeDataProvider()
    {
        return array(
            'ajax' => array(false, '', true),
            'no_file' => array([]),
            'delete' => array(array('delete' => true), '', false, true),
            'file_delete' => array(array('attributeCodeValue', 'delete' => true), 'attributeCode', false, true),
            'file_!delete' => array(array('attributeCodeValue'), 'attributeCode', false, false)
        );
    }

    /**
     * @param array $expected
     * @param string $requestScope
     * @param $mainScope
     * @dataProvider extractValueWithRequestScopeDataProvider
     */
    public function testExtractValueWithRequestScope($expected, $requestScope, $mainScope = false)
    {
        $value = 'value';
        $fileForm = $this->getClass($value, false);

        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValue(array('delete' => true))
        );
        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParams'
        )->will(
            $this->returnValue(array('delete' => true))
        );

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attributeCode')
        );

        $fileForm->setRequestScope($requestScope);

        if ($mainScope) {
            $_FILES['mainScope'] = $mainScope;
        }
        $this->assertEquals($expected, $fileForm->extractValue($this->requestMock));
        if ($mainScope) {
            unset($_FILES['mainScope']);
        }
    }

    public function extractValueWithRequestScopeDataProvider()
    {
        return array(
            'requestScope' => array([], 'requestScope'),
            'mainScope' => array(
                array('fileKey' => 'attributeValue'),
                'mainScope',
                array('fileKey' => array('attributeCode' => 'attributeValue'))
            ),
            'mainScope/scopeName' => array(
                array('fileKey' => 'attributeValue'),
                'mainScope/scopeName',
                array('fileKey' => array('scopeName' => array('attributeCode' => 'attributeValue')))
            )
        );
    }

    /**
     * @param array|bool $expected
     * @param array $value
     * @param bool $isAjax
     * @param bool $isRequired
     * @dataProvider validateValueNotToUploadDataProvider
     */
    public function testValidateValueNotToUpload($expected, $value, $isAjax = false, $isRequired = true)
    {
        $fileForm = $this->getClass($value, $isAjax);
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'isRequired'
        )->will(
            $this->returnValue($isRequired)
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue('attributeLabel')
        );

        $this->assertEquals($expected, $fileForm->validateValue($value));
    }

    public function validateValueNotToUploadDataProvider()
    {
        return array(
            'emptyValue' => array(true, [], true),
            'someValue' => array(true, array('some value')),
            'delete_someValue' => array(true, array('delete' => true, 'some value'), false, false),
            'null' => array(array('"attributeLabel" is a required value.'), null)
        );
    }

    /**
     * @param array $expected
     * @param array $value
     * @param array $parameters
     * @dataProvider validateValueToUploadDataProvider
     */
    public function testValidateValueToUpload($expected, $value, $parameters = [])
    {
        $parameters = array_merge(array('uploaded' => true, 'valid' => true), $parameters);
        $fileForm = $this->getClass($value, false);
        $fileForm->expects($this->any())->method('_isUploadedFile')->will($this->returnValue($parameters['uploaded']));
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue('File Input Field Label')
        );

        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(array('Validation error message.'))
        );
        $this->fileValidatorMock->expects(
            $this->any()
        )->method(
            'isValid'
        )->will(
            $this->returnValue($parameters['valid'])
        );
        $this->assertEquals($expected, $fileForm->validateValue($value));
    }

    public function validateValueToUploadDataProvider()
    {
        return array(
            'notValid' => array(
                array('Validation error message.'),
                array('tmp_name' => 'tempName_0001.bin', 'name' => 'realFileName.bin'),
                array('valid' => false)
            ),
            'notUploaded' => array(
                array('"realFileName.bin" is not a valid file.'),
                array('tmp_name' => 'tempName_0001.bin', 'name' => 'realFileName.bin'),
                array('uploaded' => false)
            ),
            'isValid' => array(true, array('tmp_name' => 'tempName_0001.txt', 'name' => 'realFileName.txt'))
        );
    }

    public function testCompactValueIsAjax()
    {
        $fileForm = $this->getClass('value', true);
        $this->assertSame($fileForm, $fileForm->compactValue('aValue'));
    }

    /**
     * @param string $expected
     * @param array $value
     * @dataProvider compactValueDataProvider
     */
    public function testCompactValue($expected, $value)
    {
        $fileForm = $this->getClass('value', false);
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->assertSame($expected, $fileForm->compactValue($value));
    }

    public function compactValueDataProvider()
    {
        return array('notDelete' => array('value', []), 'delete' => array('', array('delete' => true)));
    }

    public function testRestoreValue()
    {
        $value = 'value';
        $fileForm = $this->getClass($value, false);
        $this->assertEquals($value, $fileForm->restoreValue('aValue'));
    }

    /**
     * @param string $format
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValueNonJson($format)
    {
        $fileForm = $this->getClass('value', false);
        $this->assertSame('', $fileForm->outputValue($format));
    }

    public function outputValueDataProvider()
    {
        return array(
            ElementFactory::OUTPUT_FORMAT_TEXT => array(ElementFactory::OUTPUT_FORMAT_TEXT),
            ElementFactory::OUTPUT_FORMAT_ARRAY => array(ElementFactory::OUTPUT_FORMAT_ARRAY),
            ElementFactory::OUTPUT_FORMAT_HTML => array(ElementFactory::OUTPUT_FORMAT_HTML),
            ElementFactory::OUTPUT_FORMAT_ONELINE => array(ElementFactory::OUTPUT_FORMAT_ONELINE),
            ElementFactory::OUTPUT_FORMAT_PDF => array(ElementFactory::OUTPUT_FORMAT_PDF)
        );
    }

    public function testOutputValueJson()
    {
        $value = 'value';
        $urlKey = 'url_key';
        $fileForm = $this->getClass($value, false);
        $this->coreDataMock->expects(
            $this->once()
        )->method(
            'urlEncode'
        )->with(
            $this->equalTo($value)
        )->will(
            $this->returnValue($urlKey)
        );
        $expected = array('value' => $value, 'url_key' => $urlKey);
        $this->assertSame($expected, $fileForm->outputValue(ElementFactory::OUTPUT_FORMAT_JSON));
    }

    /**
     * Helper for creating the unit under test.
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @param bool $isAjax
     * @return \PHPUnit_Framework_MockObject_MockObject | File
     */
    protected function getClass($value, $isAjax)
    {
        $fileForm = $this->getMock(
            'Magento\Customer\Model\Metadata\Form\File',
            array('_isUploadedFile'),
            array(
                $this->localeMock,
                $this->loggerMock,
                $this->attributeMetadataMock,
                $this->localeResolverMock,
                $value,
                0,
                $isAjax,
                $this->coreDataMock,
                $this->fileValidatorMock,
                $this->fileSystemMock
            )
        );
        return $fileForm;
    }
}
