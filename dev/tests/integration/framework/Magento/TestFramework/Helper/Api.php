<?php
/**
 * Helper for API integration tests.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

class Api
{
    /**
     * Previous error handler
     *
     * @var mixed
     */
    protected static $_previousHandler = null;

    /**
     * Call API method via API handler.
     *
     * @param \PHPUnit_Framework_TestCase $testCase Active test case
     * @param string $path
     * @param array $params Order of items matters as they are passed to call_user_func_array
     * @return mixed
     */
    public static function call(\PHPUnit_Framework_TestCase $testCase, $path, $params = [])
    {
        $soapAdapterMock = $testCase->getMock('Magento\Api\Model\Server\Adapter\Soap', ['fault']);
        $soapAdapterMock->expects(
            $testCase->any()
        )->method(
            'fault'
        )->will(
            $testCase->returnCallback([__CLASS__, 'soapAdapterFaultCallback'])
        );

        $serverMock = $testCase->getMock('Magento\Api\Model\Server', ['getAdapter']);
        $serverMock->expects($testCase->any())->method('getAdapter')->will($testCase->returnValue($soapAdapterMock));

        $apiSessionMock = $testCase->getMock(
            'Magento\Api\Model\Session',
            ['isAllowed', 'isLoggedIn'],
            [],
            '',
            false
        );
        $apiSessionMock->expects($testCase->any())->method('isAllowed')->will($testCase->returnValue(true));
        $apiSessionMock->expects($testCase->any())->method('isLoggedIn')->will($testCase->returnValue(true));

        $handlerMock = $testCase->getMock(
            'Magento\Api\Model\Server\Handler\Soap',
            ['_getServer', '_getSession'],
            [],
            '',
            false
        );
        self::$_previousHandler = set_error_handler([$handlerMock, 'handlePhpError']);

        $handlerMock->expects($testCase->any())->method('_getServer')->will($testCase->returnValue($serverMock));
        $handlerMock->expects($testCase->any())->method('_getSession')->will($testCase->returnValue($apiSessionMock));

        array_unshift($params, 'sessionId');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $objectManager->get('Magento\Framework\Registry')->unregister('isSecureArea');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
        $result = call_user_func_array([$handlerMock, $path], $params);
        $objectManager->get('Magento\Framework\Registry')->unregister('isSecureArea');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', false);

        self::restoreErrorHandler();
        return $result;
    }

    /**
     * Call API method via API handler that raises SoapFault exception
     *
     * @param \PHPUnit_Framework_TestCase $testCase Active test case
     * @param string $path
     * @param array $params Order of items matters as they are passed to call_user_func_array
     * @param string $expectedMessage exception message
     * @return \SoapFault
     */
    public static function callWithException(
        \PHPUnit_Framework_TestCase $testCase,
        $path,
        $params = [],
        $expectedMessage = ''
    ) {
        try {
            self::call($testCase, $path, $params);
            self::restoreErrorHandler();
            $testCase->fail('Expected error exception was not raised.');
        } catch (\SoapFault $exception) {
            self::restoreErrorHandler();
            if ($expectedMessage) {
                $testCase->assertEquals($expectedMessage, $exception->getMessage());
            }
            return $exception;
        }
    }

    /**
     * Restore previously used error handler
     */
    public static function restoreErrorHandler()
    {
        set_error_handler(self::$_previousHandler);
    }

    /**
     * Throw SoapFault exception. Callback for 'fault' method of API.
     *
     * @param string $exceptionCode
     * @param string $exceptionMessage
     * @throws \SoapFault
     */
    public static function soapAdapterFaultCallback($exceptionCode, $exceptionMessage)
    {
        throw new \SoapFault($exceptionCode, $exceptionMessage);
    }

    /**
     * Convert Simple XML to array
     *
     * @param \SimpleXMLObject $xml
     * @param String $keyTrimmer
     * @return object
     *
     * In XML notation we can't have nodes with digital names in other words fallowing XML will be not valid:
     * &lt;24&gt;
     *      Default category
     * &lt;/24&gt;
     *
     * But this one will not cause any problems:
     * &lt;qwe_24&gt;
     *      Default category
     * &lt;/qwe_24&gt;
     *
     * So when we want to obtain an array with key 24 we will pass the correct XML from above and $keyTrimmer = 'qwe_';
     * As a result we will obtain an array with digital key node.
     *
     * In the other case just don't pass the $keyTrimmer.
     */
    public static function simpleXmlToArray($xml, $keyTrimmer = null)
    {
        $result = [];

        $isTrimmed = false;
        if (null !== $keyTrimmer) {
            $isTrimmed = true;
        }

        if (is_object($xml)) {
            foreach (get_object_vars($xml->children()) as $key => $node) {
                $arrKey = $key;
                if ($isTrimmed) {
                    $arrKey = str_replace($keyTrimmer, '', $key);
                }
                if (is_numeric($arrKey)) {
                    $arrKey = 'Obj' . $arrKey;
                }
                if (is_object($node)) {
                    $result[$arrKey] = self::simpleXmlToArray($node, $keyTrimmer);
                } elseif (is_array($node)) {
                    $result[$arrKey] = [];
                    foreach ($node as $nodeValue) {
                        $result[$arrKey][] = self::simpleXmlToArray($nodeValue, $keyTrimmer);
                    }
                } else {
                    $result[$arrKey] = (string)$node;
                }
            }
        } else {
            $result = (string)$xml;
        }
        return $result;
    }

    /**
     * Check specific fields value in some entity data.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param array $expectedData
     * @param array $actualData
     * @param array $fieldsToCompare To be able to compare fields from loaded model with fields from API response
     *     this parameter provides fields mapping.
     *     Array can store model field name $entityField mapped on field name in API response.
     *     $fieldsToCompare format is:
     *     $fieldsToCompare = array($modelFieldName => $apiResponseFieldName);
     *     Example:
     *     $fieldsToCompare = array(
     *         'entity_id' => 'product_id',
     *         'sku',
     *         'attribute_set_id' => 'set',
     *         'type_id' => 'type',
     *         'category_ids',
     *     );
     */
    public static function checkEntityFields(
        \PHPUnit_Framework_TestCase $testCase,
        array $expectedData,
        array $actualData,
        array $fieldsToCompare = []
    ) {
        $fieldsToCompare = !empty($fieldsToCompare) ? $fieldsToCompare : array_keys($expectedData);
        foreach ($fieldsToCompare as $entityField => $field) {
            $testCase->assertEquals(
                $expectedData[is_numeric($entityField) ? $field : $entityField],
                $actualData[$field],
                sprintf('"%s" filed has invalid value.', $field)
            );
        }
    }
}
