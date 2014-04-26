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

namespace Magento\Framework\Validator;

/**
 * Test case for \Magento\Framework\Validator\StringLength
 */
class StringLengthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Validator\StringLength
     */
    protected $_validator;

    protected function setUp()
    {
        $this->_validator = new \Magento\Framework\Validator\StringLength();
    }

    public function testDefaultEncoding()
    {
        $this->assertEquals('UTF-8', $this->_validator->getEncoding());
    }

    /**
     * @dataProvider isValidDataProvider
     * @param string $value
     * @param int $maxLength
     * @param bool $isValid
     */
    public function testIsValid($value, $maxLength, $isValid)
    {
        $this->_validator->setMax($maxLength);
        $this->assertEquals($isValid, $this->_validator->isValid($value));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return array(
            array('строка', 6, true),
            array('строка', 5, false),
            array('string', 6, true),
            array('string', 5, false)
        );
    }
}
