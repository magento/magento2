<?php
/**
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
// @codingStandardsIgnoreStart
namespace {
    $mockTranslate = false;
}

namespace Magento\Framework\Exception {
    // @codingStandardsIgnoreEnd

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return string
     */
    function __()
    {
        global $mockTranslate;
        $prefix = '';
        if (isset($mockTranslate) && $mockTranslate === true) {
            $prefix = 'TRANSLATED: ';
        }
        return $prefix . call_user_func_array('\__', func_get_args());
    }

    /**
     * Class InputExceptionTest
     */
    class InputExceptionTest extends \PHPUnit_Framework_TestCase
    {
        const TRANSLATED_PREFIX = 'TRANSLATED: ';

        public function setUp()
        {
            global $mockTranslate;
            $mockTranslate = true;
        }

        /**
         * Verify that the constructor creates a single instance of InputException with the proper
         * message and array of parameters.
         *
         * @return void
         */
        public function testConstructor()
        {
            $params = ['fieldName' => 'quantity', 'value' => -100, 'minValue' => 0];
            $inputException = new InputException(InputException::INVALID_FIELD_MIN_VALUE, $params);

            $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $inputException->getRawMessage());
            $this->assertStringMatchesFormat('%s greater than or equal to %s', $inputException->getMessage());
            $this->assertEquals(
                'The quantity value of "-100" must be greater than or equal to 0.',
                $inputException->getLogMessage()
            );
        }

        /**
         * Verify that adding multiple errors works correctly.
         *
         * @return void
         */
        public function testAddError()
        {
            $inputException = new InputException();

            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getRawMessage());
            $this->assertEquals(
                self::TRANSLATED_PREFIX . InputException::DEFAULT_MESSAGE,
                $inputException->getMessage()
            );
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getLogMessage());

            $this->assertFalse($inputException->wasErrorAdded());
            $this->assertCount(0, $inputException->getErrors());

            $inputException->addError(
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => 'weight', 'value' => -100, 'minValue' => 1]
            );
            $this->assertTrue($inputException->wasErrorAdded());
            $this->assertCount(0, $inputException->getErrors());

            $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $inputException->getRawMessage());
            $this->assertEquals(
                self::TRANSLATED_PREFIX .
                'The weight value of "-100" must be greater than or equal to 1.',
                $inputException->getMessage()
            );
            $this->assertEquals(
                'The weight value of "-100" must be greater than or equal to 1.',
                $inputException->getLogMessage()
            );

            $inputException->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'name']);
            $this->assertTrue($inputException->wasErrorAdded());
            $this->assertCount(2, $inputException->getErrors());

            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getRawMessage());
            $this->assertEquals(
                self::TRANSLATED_PREFIX . InputException::DEFAULT_MESSAGE,
                $inputException->getMessage()
            );
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getLogMessage());

            $errors = $inputException->getErrors();
            $this->assertCount(2, $errors);

            $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $errors[0]->getRawMessage());
            $this->assertEquals(
                self::TRANSLATED_PREFIX .
                'The weight value of "-100" must be greater than or equal to 1.',
                $errors[0]->getMessage()
            );
            $this->assertEquals(
                'The weight value of "-100" must be greater than or equal to 1.',
                $errors[0]->getLogMessage()
            );

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[1]->getRawMessage());
            $this->assertEquals(self::TRANSLATED_PREFIX . 'name is a required field.', $errors[1]->getMessage());
            $this->assertEquals('name is a required field.', $errors[1]->getLogMessage());
        }

        /**
         * Verify the message and params are not used to determine the call count
         *
         * @return void
         */
        public function testAddErrorWithSameMessage()
        {
            $rawMessage = 'Foo "%var"';
            $params = ['var' => 'Bar'];
            $expectedProcessedMessage = 'Foo "Bar"';
            $inputException = new InputException($rawMessage, $params);
            $this->assertEquals($rawMessage, $inputException->getRawMessage());
            $this->assertEquals(self::TRANSLATED_PREFIX . $expectedProcessedMessage, $inputException->getMessage());
            $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
            $this->assertFalse($inputException->wasErrorAdded());
            $this->assertCount(0, $inputException->getErrors());

            $inputException->addError($rawMessage, $params);
            $this->assertEquals(self::TRANSLATED_PREFIX . $expectedProcessedMessage, $inputException->getMessage());
            $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
            $this->assertTrue($inputException->wasErrorAdded());
            $this->assertCount(0, $inputException->getErrors());

            $inputException->addError($rawMessage, $params);
            $this->assertEquals(self::TRANSLATED_PREFIX . $expectedProcessedMessage, $inputException->getMessage());
            $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
            $this->assertTrue($inputException->wasErrorAdded());

            $errors = $inputException->getErrors();
            $this->assertCount(2, $errors);
            $this->assertEquals(self::TRANSLATED_PREFIX . $expectedProcessedMessage, $errors[0]->getMessage());
            $this->assertEquals($expectedProcessedMessage, $errors[0]->getLogMessage());
            $this->assertEquals(self::TRANSLATED_PREFIX . $expectedProcessedMessage, $errors[1]->getMessage());
            $this->assertEquals($expectedProcessedMessage, $errors[1]->getLogMessage());
        }
    }
}
