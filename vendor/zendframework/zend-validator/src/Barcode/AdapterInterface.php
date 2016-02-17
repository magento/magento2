<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Barcode;

interface AdapterInterface
{
    /**
     * Checks the length of a barcode
     *
     * @param  string $value  The barcode to check for proper length
     * @return bool
     */
    public function hasValidLength($value);

    /**
     * Checks for allowed characters within the barcode
     *
     * @param  string $value The barcode to check for allowed characters
     * @return bool
     */
    public function hasValidCharacters($value);

    /**
     * Validates the checksum
     *
     * @param string $value The barcode to check the checksum for
     * @return bool
     */
    public function hasValidChecksum($value);

    /**
     * Returns the allowed barcode length
     *
     * @return int|array
     */
    public function getLength();

    /**
     * Returns the allowed characters
     *
     * @return int|string|array
     */
    public function getCharacters();

    /**
     * Returns if barcode uses a checksum
     *
     * @return bool
     */
    public function getChecksum();

    /**
     * Sets the checksum validation, if no value is given, the actual setting is returned
     *
     * @param  bool $check
     * @return AbstractAdapter|bool
     */
    public function useChecksum($check = null);
}
