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

namespace Magento\Test\Integrity\App\Language;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array(
            "Element 'code': [facet 'pattern'] The value 'e_GB' is not accepted by the pattern",
            "Element 'code': 'e_GB' is not a valid value of the atomic type 'codeType'",
            "Element 'vendor': [facet 'pattern'] The value 'Magento' is not accepted by the pattern",
            "Element 'vendor': 'Magento' is not a valid value of the atomic type",
            "Element 'sort_odrer': This element is not expected. Expected is",
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/lib/internal/Magento/Framework/App/Language/package.xsd';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/known_valid.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/known_invalid.xml';
    }

    /**
     * {@inheritdoc}
     */
    protected function _getKnownValidPartialXml()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getFileXsd()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getKnownInvalidPartialXml()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getXmlName()
    {
        return;
    }
}
