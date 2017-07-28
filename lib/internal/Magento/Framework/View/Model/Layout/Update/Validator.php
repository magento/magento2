<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\Layout\Update;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Dom\ValidationSchemaException;

/**
 * Validator for custom layout update
 *
 * Validator checked XML validation and protected expressions
 * @since 2.0.0
 */
class Validator extends \Zend_Validate_Abstract
{
    const XML_INVALID = 'invalidXml';

    const XSD_INVALID = 'invalidXsd';

    const HELPER_ARGUMENT_TYPE = 'helperArgumentType';

    const UPDATER_MODEL = 'updaterModel';

    const XML_NAMESPACE_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    const LAYOUT_SCHEMA_PAGE_HANDLE = 'page_layout';

    const LAYOUT_SCHEMA_MERGED = 'layout_merged';

    /**
     * The Magento SimpleXml object
     *
     * @var \Magento\Framework\Simplexml\Element
     * @since 2.0.0
     */
    protected $_value;

    /**
     * Protected expressions
     *
     * @var array
     * @since 2.0.0
     */
    protected $_protectedExpressions = [
        self::HELPER_ARGUMENT_TYPE => '//*[@xsi:type="helper"]',
        self::UPDATER_MODEL => '//updater',
    ];

    /**
     * XSD Schemas for Layout Update validation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_xsdSchemas;

    /**
     * @var \Magento\Framework\Config\DomFactory
     * @since 2.0.0
     */
    protected $_domConfigFactory;

    /**
     * @param \Magento\Framework\Config\DomFactory $domConfigFactory
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\DomFactory $domConfigFactory,
        UrnResolver $urnResolver
    ) {
        $this->_domConfigFactory = $domConfigFactory;
        $this->_initMessageTemplates();
        $this->_xsdSchemas = [
            self::LAYOUT_SCHEMA_PAGE_HANDLE => $urnResolver->getRealPath(
                'urn:magento:framework:View/Layout/etc/page_layout.xsd'
            ),
            self::LAYOUT_SCHEMA_MERGED => $urnResolver->getRealPath(
                'urn:magento:framework:View/Layout/etc/layout_merged.xsd'
            ),
        ];
    }

    /**
     * Initialize messages templates with translating
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = [
                self::HELPER_ARGUMENT_TYPE => (string)new \Magento\Framework\Phrase(
                    'Helper arguments should not be used in custom layout updates.'
                ),
                self::UPDATER_MODEL => (string)new \Magento\Framework\Phrase(
                    'Updater model should not be used in custom layout updates.'
                ),
                self::XML_INVALID => (string)new \Magento\Framework\Phrase(
                    'Please correct the XML data and try again. %value%'
                ),
                self::XSD_INVALID => (string)new \Magento\Framework\Phrase(
                    'Please correct the XSD data and try again. %value%'
                ),
            ];
        }
        return $this;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method throws exception, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param string $value
     * @param string $schema
     * @param bool $isSecurityCheck
     * @return bool
     * @throws \Exception
     * @since 2.0.0
     */
    public function isValid($value, $schema = self::LAYOUT_SCHEMA_PAGE_HANDLE, $isSecurityCheck = true)
    {
        try {
            //wrap XML value in the "layout" and "handle" tags to make it validatable
            $value = '<layout xmlns:xsi="' . self::XML_NAMESPACE_XSI . '">' . $value . '</layout>';
            $this->_domConfigFactory->createDom(['xml' => $value, 'schemaFile' => $this->_xsdSchemas[$schema]]);

            if ($isSecurityCheck) {
                $value = new \Magento\Framework\Simplexml\Element($value);
                $value->registerXPathNamespace('xsi', self::XML_NAMESPACE_XSI);
                foreach ($this->_protectedExpressions as $key => $xpr) {
                    if ($value->xpath($xpr)) {
                        $this->_error($key);
                    }
                }
                $errors = $this->getMessages();
                if (!empty($errors)) {
                    return false;
                }
            }
        } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
            $this->_error(self::XML_INVALID, $e->getMessage());
            throw $e;
        } catch (ValidationSchemaException $e) {
            $this->_error(self::XSD_INVALID, $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->_error(self::XML_INVALID);
            throw $e;
        }
        return true;
    }
}
