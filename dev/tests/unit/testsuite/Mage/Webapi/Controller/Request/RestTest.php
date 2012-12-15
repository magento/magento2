<?php
/**
 * Test Webapi Request model
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Request_RestTest extends PHPUnit_Framework_TestCase
{
    /**
     * Request mock
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_interpreterFactory;

    protected function setUp()
    {
        parent::setUp();
        /** Prepare mocks for request constructor arguments. */
        $this->_interpreterFactory = $this->getMockBuilder('Mage_Webapi_Controller_Request_Rest_Interpreter_Factory')
            ->setMethods(array('interpret', 'get'))
            ->disableOriginalConstructor()
            ->getMock();
        $helper = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $helper->expects($this->any())->method('__')->will($this->returnArgument(0));
        /** Instantiate request. */
        // TODO: Get rid of SUT mocks.
        $this->_request = $this->getMock(
            'Mage_Webapi_Controller_Request_Rest',
            array('getHeader', 'getMethod', 'isGet', 'isPost', 'isPut', 'isDelete', 'getRawBody'),
            array($this->_interpreterFactory, $helper)
        );
    }

    protected function tearDown()
    {
        unset($this->_request);
        parent::tearDown();
    }

    /**
     * Test for getAcceptTypes() method
     *
     * @dataProvider providerAcceptType
     * @param string $acceptHeader Value of Accept HTTP header
     * @param array $expectedResult Method call result
     */
    public function testGetAcceptTypes($acceptHeader, $expectedResult)
    {
        $this->_request
            ->expects($this->once())
            ->method('getHeader')
            ->with('Accept')
            ->will($this->returnValue($acceptHeader));
        /** @var Mage_Webapi_Controller_Request_Rest _requestMock */
        $this->assertSame($expectedResult, $this->_request->getAcceptTypes());
    }

    /**
     * Test for getBodyParams() method.
     */
    public function testGetBodyParams()
    {
        $params = array('a' => 123, 'b' => 145);
        $this->_prepareSutForGetBodyParamsTest($params);
        $this->assertEquals(
            $params,
            $this->_request->getBodyParams(),
            'Body parameters were retrieved incorrectly.'
        );
    }

    /**
     * Prepare SUT for GetBodyParams() method mock.
     *
     * @param array $params
     */
    protected function _prepareSutForGetBodyParamsTest($params)
    {
        $rawBody = 'rawBody';
        $this->_request->expects($this->once())
            ->method('getRawBody')
            ->will($this->returnValue($rawBody));
        $contentType = 'contentType';
        $this->_request
            ->expects($this->once())
            ->method('getHeader')
            ->with('Content-Type')
            ->will($this->returnValue($contentType));
        $interpreter = $this->getMockBuilder('Mage_Webapi_Controller_Request_Rest_Interpreter_Json')
            ->disableOriginalConstructor()
            ->setMethods(array('interpret'))
            ->getMock();
        $interpreter->expects($this->once())
            ->method('interpret')
            ->with($rawBody)
            ->will($this->returnValue($params));
        $this->_interpreterFactory->expects($this->once())
            ->method('get')
            ->with($contentType)
            ->will($this->returnValue($interpreter));
    }

    /**
     * Test for getContentType() method
     *
     * @dataProvider providerContentType
     * @param string $contentTypeHeader 'Content-Type' header value
     * @param string $contentType Appropriate content type for header value
     * @param string|boolean $exceptionMessage Exception message (boolean FALSE if exception is not expected)
     */
    public function testGetContentType($contentTypeHeader, $contentType, $exceptionMessage = false)
    {
        $this->_request
            ->expects($this->once())
            ->method('getHeader')
            ->with('Content-Type')
            ->will($this->returnValue($contentTypeHeader));

        try {
            $this->assertEquals($contentType, $this->_request->getContentType());
        } catch (Mage_Webapi_Exception $e) {
            if ($exceptionMessage) {
                $this->assertEquals(
                    $exceptionMessage,
                    $e->getMessage(),
                    'Exception message does not match expected one'
                );
                return;
            } else {
                $this->fail('Exception thrown on valid header: ' . $e->getMessage());
            }
        }
        if ($exceptionMessage) {
            $this->fail('Expected exception was not raised');
        }
    }

    /**
     * Test for getOperation() method
     *
     * @dataProvider providerRequestMethod
     * @param string $requestMethod Request method
     * @param string $crudOperation CRUD operation name
     * @param string|boolean $exceptionMessage Exception message (boolean FALSE if exception is not expected)
     */
    public function testGetOperation($requestMethod, $crudOperation, $exceptionMessage = false)
    {
        $expectedMethod = false;
        switch ($requestMethod) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $expectedMethod = 'is' . ucfirst($requestMethod);
                break;
        }

        if ($expectedMethod) {
            $this->_request
                ->expects($this->once())
                ->method($expectedMethod)
                ->will($this->returnValue(true));
        }

        $this->_request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($requestMethod));

        try {
            $this->assertEquals($crudOperation, $this->_request->getHttpMethod());
        } catch (Mage_Webapi_Exception $e) {
            if ($exceptionMessage) {
                $this->assertEquals(
                    $exceptionMessage,
                    $e->getMessage(),
                    'Exception message does not match expected one'
                );
                return;
            } else {
                $this->fail('Exception thrown on valid header: ' . $e->getMessage());
            }
        }
        if ($exceptionMessage) {
            $this->fail('Expected exception was not raised');
        }
    }

    /**
     * Test for getResourceType() method
     *
     */
    public function testGetResourceType()
    {
        $this->assertNull($this->_request->getResourceType());
        $resource = 'test_resource';
        $this->_request->setResourceType($resource);
        $this->assertEquals($resource, $this->_request->getResourceType());
    }

    /**
     * Data provider for testGetAcceptTypes()
     *
     * @return array
     */
    public function providerAcceptType()
    {
        return array(
            // Each element is: array(Accept HTTP header value, expected result))
            array('', array()),
            array(
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                array('text/html', 'application/xhtml+xml', 'application/xml', '*/*')
            ),
            array(
                'text/html, application/*, text, */*',
                array('text/html', 'application/*', 'text', '*/*')
            ),
            array(
                'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp,'
                    . ' image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1',
                array(
                    'text/html',
                    'application/xhtml+xml',
                    'image/png',
                    'image/webp',
                    'image/jpeg',
                    'image/gif',
                    'image/x-xbitmap',
                    'application/xml',
                    '*/*'
                )
            )
        );
    }

    /**
     * Data provider for testGetContentType()
     *
     * @return array
     */
    public function providerContentType()
    {
        return array(
            // Each element is: array(Content-Type header value, content-type part[, expected exception message])
            array('', null, 'Content-Type header is empty'),
            array('_?', null, 'Invalid Content-Type header'),
            array('application/x-www-form-urlencoded; charset=UTF-8', 'application/x-www-form-urlencoded'),
            array('application/x-www-form-urlencoded; charset=utf-8', 'application/x-www-form-urlencoded'),
            array('text/html; charset=uTf-8', 'text/html'),
            array('text/html; charset=', null, 'Invalid Content-Type header'),
            array('text/html;', null, 'Invalid Content-Type header'),
            array('application/dialog.dot-info7+xml', 'application/dialog.dot-info7+xml'),
            array('application/x-www-form-urlencoded; charset=cp1251', null, 'UTF-8 is the only supported charset')
        );
    }

    /**
     * Data provider for testGetOperation()
     *
     * @return array
     */
    public function providerRequestMethod()
    {
        return array(
            // Each element is: array(Request method, CRUD operation name[, expected exception message])
            array('INVALID_METHOD', null, 'Invalid request method'),
            array('GET', Mage_Webapi_Controller_Request_Rest::HTTP_METHOD_GET),
            array('POST', Mage_Webapi_Controller_Request_Rest::HTTP_METHOD_CREATE),
            array('PUT', Mage_Webapi_Controller_Request_Rest::HTTP_METHOD_UPDATE),
            array('DELETE', Mage_Webapi_Controller_Request_Rest::HTTP_METHOD_DELETE)
        );
    }

    /**
     * @dataProvider dataProviderTestGetActionTypeByMethod
     * @param string $methodName
     * @param string $expectedActionType
     */
    public function testGetActionTypeByMethod($methodName, $expectedActionType)
    {
        $this->assertEquals(
            $expectedActionType,
            Mage_Webapi_Controller_Request_Rest::getActionTypeByOperation($methodName),
            "Action type was identified incorrectly by method name."
        );
    }

    public static function dataProviderTestGetActionTypeByMethod()
    {
        return array(
            array(
                Mage_Webapi_Controller_ActionAbstract::METHOD_CREATE,
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_COLLECTION
            ),
            array(
                Mage_Webapi_Controller_ActionAbstract::METHOD_DELETE,
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_ITEM
            ),
        );
    }

    public function testGetActionTypeException()
    {
        $methodName = 'invalidMethodV1';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('The "%s" method is not a valid resource method.', $methodName)
        );
        Mage_Webapi_Controller_Request_Rest::getActionTypeByOperation($methodName);
    }

    public function testGetResourceVersion()
    {
        $this->_request->setParam('resourceVersion', 'v1');
        $this->assertEquals(1, $this->_request->getResourceVersion(), 'Version number was missed.');
    }

    public function testGetResourceVersionVersionIsNotSpecifiedException()
    {
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Resource version is not specified or invalid one is specified',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_request->getResourceVersion();
    }

    public function testGetResourceName()
    {
        $resourceName = 'resourceName';
        $this->_request->setResourceName($resourceName);
        $this->assertEquals($resourceName, $this->_request->getResourceName());
    }

    public function testGetOperationNameMethodNotExistException()
    {
        /** Prepare mocks for SUT constructor. */
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Requested method does not exist.',
            Mage_Webapi_Exception::HTTP_NOT_FOUND
        );
        $this->_request->expects($this->once())->method('isPost')->will($this->returnValue(true));
        $this->_request->expects($this->once())->method('getMethod')->will($this->returnValue('POST'));
        $this->_request->setResourceType(Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_ITEM);
        /** Initialize SUT. */
        $this->_request->getOperationName();
    }

    /**
     * @dataProvider getOperationNameProvider
     */
    public function testGetOperationName($resourceType, $httpMethod, $operationName)
    {
        /** Prepare mocks for SUT constructor. */
        $this->_request->setResourceType($resourceType);
        $this->_request->expects($this->once())->method('isPost')->will($this->returnValue(true));
        $this->_request->expects($this->once())->method('getMethod')->will($this->returnValue($httpMethod));
        $this->_request->setResourceName('resourceName');

        /** Initialize SUT. */
        $this->assertEquals(
            $operationName,
            $this->_request->getOperationName(),
            'Method getOperationName() retrieved invalid resource name. Check $restMethodMap variable.'
        );
    }

    /**
     * Success getOperationName() method data provider
     *
     * @return array
     */
    public function getOperationNameProvider()
    {
        return array(
            'GET request with action type: item.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_ITEM,
                'GET',
                'resourceNameGet',
            ),
            'PUT request with action type: item.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_ITEM,
                'PUT',
                'resourceNameUpdate',
            ),
            'DELETE request with action type: item.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_ITEM,
                'DELETE',
                'resourceNameDelete',
            ),
            'GET request with action type: collection.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_COLLECTION,
                'GET',
                'resourceNameList',
            ),
            'PUT request with action type: collection.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_COLLECTION,
                'PUT',
                'resourceNameMultiUpdate',
            ),
            'DELETE request with action type: collection.' => array(
                Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_COLLECTION,
                'DELETE',
                'resourceNameMultiDelete',
            ),
        );
    }

    public function testGetOperationNameWithCreateMethod()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_prepareSutForGetOperationNameWithCreateMethod();
        $this->_prepareSutForGetBodyParamsTest(array('key_1' => 'value', 'key_2' => 'value'));

        /** Initialize SUT. */
        $this->assertEquals(
            'resourceNameCreate',
            $this->_request->getOperationName(),
            'Invalid resource name for create method.'
        );
    }

    public function testGetOperationNameWithMultiCreateMethod()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_prepareSutForGetOperationNameWithCreateMethod();
        $params = array(
            array('key_1' => 'value 1'),
            array('key_2' => 'value 2'),
        );
        $this->_prepareSutForGetBodyParamsTest($params);

        /** Initialize SUT. */
        $this->assertEquals(
            'resourceNameMultiCreate',
            $this->_request->getOperationName(),
            'Invalid resource name for multi create method.'
        );
    }

    /**
     * Prepare SUT for GetOperationName() with create action.
     */
    protected function _prepareSutForGetOperationNameWithCreateMethod()
    {
        $this->_request->setResourceType(Mage_Webapi_Controller_Request_Rest::ACTION_TYPE_COLLECTION);
        $this->_request->expects($this->once())->method('isPost')->will($this->returnValue(true));
        $this->_request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->_request->setResourceName('resourceName');
    }
}
