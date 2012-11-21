<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Magento Comercial License information class
 *
 */
class Mcl extends LicenseAbstract
{
    /**
     * Prepare short information about license
     *
     * @return string
     */
    public function getNotice()
    {
        return <<<EOT
 * Magento Commercial Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Commercial Edition License
 * that is available at: http://www.magentocommerce.com/license/commercial-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
EOT;

    }

    /**
     * Prepare data for phpdoc attribute "license"
     *
     * @return string
     */
    public function getLink()
    {
        return 'http://www.magentocommerce.com/license/commercial-edition';
    }
}
