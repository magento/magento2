<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Osl.php';
/**
 * Phoenix OSL license information class
 *
 */
class Phoenix extends Osl
{
    /**
     * Prepare data for phpdoc attribute "copyright"
     *
     * @return string
     */
    public function getCopyright()
    {
        $year = date('Y');
        return "Copyright (c) {$year} Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)";
    }
}
