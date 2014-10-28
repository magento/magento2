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
namespace Magento\Framework\Exception;

class NoSuchEntityExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $exception = new NoSuchEntityException();
        $this->assertEquals('No such entity.', $exception->getRawMessage());
        $this->assertEquals('No such entity.', $exception->getMessage());
        $this->assertEquals('No such entity.', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            NoSuchEntityException::MESSAGE_SINGLE_FIELD,
            ['fieldName' => 'field', 'fieldValue' => 'value']
        );
        $this->assertEquals('No such entity with field = value', $exception->getMessage());
        $this->assertEquals(NoSuchEntityException::MESSAGE_SINGLE_FIELD, $exception->getRawMessage());
        $this->assertEquals('No such entity with field = value', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            [
                'fieldName' => 'field1',
                'fieldValue' => 'value1',
                'field2Name' => 'field2',
                'field2Value' => 'value2'
            ]
        );
        $this->assertEquals(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            $exception->getRawMessage()
        );
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getMessage());
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getLogMessage());
    }
}
