<?php
/**
 * Test Webapi Error Processor.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit;

use \Magento\Framework\Webapi\ErrorProcessor;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Phrase;

class ErrorProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ErrorProcessor */
    protected $_errorProcessor;

    /** @var \Magento\Framework\Json\Encoder */
    protected $encoderMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appStateMock;

    /** @var \Psr\Log\LoggerInterface */
    protected $_loggerMock;

    protected function setUp()
    {
        /** Set up mocks for SUT. */
        $this->encoderMock = $this->getMockBuilder(\Magento\Framework\Json\Encoder::class)
            ->disableOriginalConstructor()
            ->setMethods(['encode'])
            ->getMock();

        $this->_appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** Initialize SUT. */
        $this->_errorProcessor = new ErrorProcessor(
            $this->encoderMock,
            $this->_appStateMock,
            $this->_loggerMock,
            $filesystemMock
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_errorProcessor);
        unset($this->encoderMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    /**
     * Test render method in JSON format.
     *
     * @return void
     */
    public function testRenderJson()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Assert that jsonEncode method will be executed once. */
        $this->encoderMock->expects(
            $this->once()
        )->method(
            'encode'
        )->will(
            $this->returnCallback([$this, 'callbackJsonEncode'], $this->returnArgument(0))
        );
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->renderErrorMessage('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":500,"message":"Message"}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in JSON.');
    }

    /**
     * Callback function for RenderJson and RenderJsonInDeveloperMode tests.
     *
     * Method encodes data to JSON and returns it.
     *
     * @param array $data
     * @return string
     */
    public function callbackJsonEncode($data)
    {
        return json_encode($data);
    }

    /**
     * Test render method in JSON format with turned on developer mode.
     * @return void
     */
    public function testRenderJsonInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Mock app to return enabled developer mode flag. */
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        /** Assert that jsonEncode method will be executed once. */
        $this->encoderMock->expects(
            $this->once()
        )->method(
            'encode'
        )->will(
            $this->returnCallback([$this, 'callbackJsonEncode'], $this->returnArgument(0))
        );
        ob_start();
        $this->_errorProcessor->renderErrorMessage('Message', 'Message trace.', 401);
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":401,"message":"Message","trace":"Message trace."}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in JSON.');
    }

    /**
     * Test render method in XML format.
     * @return void
     */
    public function testRenderXml()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->renderErrorMessage('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>500</code>' .
            '<message><![CDATA[Message]]></message></data_item></error></messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in XML.');
    }

    /**
     * Test render method in XML format with turned on developer mode.
     * @return void
     */
    public function testRenderXmlInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Mock app to return enabled developer mode flag. */
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->renderErrorMessage('Message', 'Trace message.', 401);
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>401</code><message>' .
            '<![CDATA[Message]]></message><trace><![CDATA[Trace message.]]></trace></data_item></error>' .
            '</messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Invalid rendering in XML with turned on developer mode.');
    }

    /**
     * Test default render format is JSON.
     * @return void
     */
    public function testRenderDefaultFormat()
    {
        /** Set undefined rendering format. */
        $_SERVER['HTTP_ACCEPT'] = 'undefined';
        /** Assert that jsonEncode method will be executed at least once. */
        $this->encoderMock->expects($this->atLeastOnce())->method('encode');
        $this->_errorProcessor->renderErrorMessage('Message');
    }

    /**
     * Test maskException method with turned on developer mode.
     * @return void
     */
    public function testMaskExceptionInDeveloperMode()
    {
        /** Mock app isDeveloperMode to return true. */
        $this->_appStateMock->expects($this->once())->method('getMode')->will($this->returnValue('developer'));
        /** Init Logical exception. */
        $errorMessage = 'Error Message';
        $logicalException = new \LogicException($errorMessage);
        /** Assert that Logic exception is converted to WebapiException without message obfuscation. */
        $maskedException = $this->_errorProcessor->maskException($logicalException);
        $this->assertInstanceOf(\Magento\Framework\Webapi\Exception::class, $maskedException);
        $this->assertEquals(
            $errorMessage,
            $maskedException->getMessage(),
            'Exception was masked incorrectly in developer mode.'
        );
    }

    /**
     * Test sendResponse method with various exceptions
     *
     * @param \Exception $exception
     * @param int $expectedHttpCode
     * @param string $expectedMessage
     * @param array $expectedDetails
     * @return void
     * @dataProvider dataProviderForSendResponseExceptions
     */
    public function testMaskException($exception, $expectedHttpCode, $expectedMessage, $expectedDetails)
    {
        /** Assert that exception was logged. */
        // TODO:MAGETWO-21077 $this->_loggerMock->expects($this->once())->method('critical');
        $maskedException = $this->_errorProcessor->maskException($exception);
        $this->assertMaskedException(
            $maskedException,
            $expectedHttpCode,
            $expectedMessage,
            $expectedDetails
        );
    }

    /**
     * Test logged exception is the same as the thrown one in production mode
     */
    public function testCriticalExceptionStackTrace()
    {
        $thrownException = new \Exception('', 0);

        $this->_loggerMock->expects($this->once())
            ->method('critical')
            ->will(
                $this->returnCallback(
                    function (\Exception $loggedException) use ($thrownException) {
                        $this->assertSame($thrownException, $loggedException->getPrevious());
                    }
                )
            );
        $this->_errorProcessor->maskException($thrownException);
    }

    /**
     * @return array
     */
    public function dataProviderForSendResponseExceptions()
    {
        return [
            'NoSuchEntityException' => [
                new NoSuchEntityException(
                    new Phrase(
                        'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                        [
                            'fieldName' => 'detail1',
                            'fieldValue' => 'value1',
                            'field2Name' => 'resource_id',
                            'field2Value' => 'resource10',
                        ]
                    )
                ),
                \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND,
                'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                [
                    'fieldName' => 'detail1',
                    'fieldValue' => 'value1',
                    'field2Name' => 'resource_id',
                    'field2Value' => 'resource10',
                ],
            ],
            'NoSuchEntityException (Empty message)' => [
                new NoSuchEntityException(),
                WebapiException::HTTP_NOT_FOUND,
                'No such entity.',
                [],
            ],
            'AuthorizationException' => [
                new AuthorizationException(
                    new Phrase(
                        'Consumer %consumer_id is not authorized to access %resources',
                        ['consumer_id' => '3', 'resources' => '4']
                    )
                ),
                WebapiException::HTTP_UNAUTHORIZED,
                'Consumer %consumer_id is not authorized to access %resources',
                ['consumer_id' => '3', 'resources' => '4'],
            ],
            'Exception' => [
                new \Exception('Non service exception', 5678),
                WebapiException::HTTP_INTERNAL_ERROR,
                'Internal Error. Details are available in Magento log file. Report ID:',
                [],
            ]
        ];
    }

    /**
     * Assert that masked exception contains expected data.
     *
     * @param \Exception $maskedException
     * @param int $expectedHttpCode
     * @param string $expectedMessage
     * @param array $expectedDetails
     * @return void
     */
    public function assertMaskedException(
        $maskedException,
        $expectedHttpCode,
        $expectedMessage,
        $expectedDetails
    ) {
        /** All masked exceptions must be WebapiException */
        $expectedType = \Magento\Framework\Webapi\Exception::class;
        $this->assertInstanceOf(
            $expectedType,
            $maskedException,
            "Masked exception type is invalid: expected '{$expectedType}', given '" . get_class(
                $maskedException
            ) . "'."
        );
        /** @var $maskedException WebapiException */
        $this->assertEquals(
            $expectedHttpCode,
            $maskedException->getHttpCode(),
            "Masked exception HTTP code is invalid: expected '{$expectedHttpCode}', " .
            "given '{$maskedException->getHttpCode()}'."
        );
        $this->assertContains(
            $expectedMessage,
            $maskedException->getMessage(),
            "Masked exception message is invalid: expected '{$expectedMessage}', " .
            "given '{$maskedException->getMessage()}'."
        );
        $this->assertEquals($expectedDetails, $maskedException->getDetails(), "Masked exception details are invalid.");
    }
}
