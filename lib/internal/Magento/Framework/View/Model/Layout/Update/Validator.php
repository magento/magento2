<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\Layout\Update;

use Laminas\Validator\AbstractValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Validator for custom layout update
 *
 * Validator checked XML validation and protected expressions
 */
class Validator extends AbstractValidator
{
    public const XML_INVALID = 'invalidXml';

    public const XSD_INVALID = 'invalidXsd';

    public const HELPER_ARGUMENT_TYPE = 'helperArgumentType';

    public const UPDATER_MODEL = 'updaterModel';

    public const XML_NAMESPACE_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    public const LAYOUT_SCHEMA_PAGE_HANDLE = 'page_layout';

    public const LAYOUT_SCHEMA_MERGED = 'layout_merged';

    /**
     * The Magento SimpleXml object
     *
     * @var \Magento\Framework\Simplexml\Element
     */
    protected $_value;

    /**
     * @var array
     */
    protected $_protectedExpressions = [
        self::HELPER_ARGUMENT_TYPE => '//*[@xsi:type="helper"]',
        self::UPDATER_MODEL => '//updater',
    ];

    /**
     * XSD Schemas for Layout Update validation
     *
     * @var array
     */
    protected $_xsdSchemas;

    /**
     * @var DomFactory
     */
    protected $_domConfigFactory;

    /**
     * @var ValidationStateInterface
     */
    protected $validationState;

    /**
     * @var array
     */
    protected $messageTemplates;

    /**
     * @param DomFactory $domConfigFactory
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param ValidationStateInterface $validationState
     */
    public function __construct(
        DomFactory $domConfigFactory,
        UrnResolver $urnResolver,
        ?ValidationStateInterface $validationState = null
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
        $this->validationState = $validationState
            ?: ObjectManager::getInstance()->get(ValidationStateInterface::class);

        parent::__construct();
    }

    /**
     * Initialize messages templates with translating
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        if (!$this->messageTemplates) {
            $this->messageTemplates = [
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
     */
    public function isValid($value, $schema = self::LAYOUT_SCHEMA_PAGE_HANDLE, $isSecurityCheck = true)
    {
        try {
            //wrap XML value in the "layout" and "handle" tags to make it validatable
            $value = '<layout xmlns:xsi="' . self::XML_NAMESPACE_XSI . '">' . $value . '</layout>';
            $this->_domConfigFactory->createDom(
                [
                    'xml' => $value,
                    'schemaFile' => $this->_xsdSchemas[$schema],
                    'validationState' => $this->validationState,
                ]
            );

            if ($isSecurityCheck) {
                $value = new \Magento\Framework\Simplexml\Element($value);
                $value->registerXPathNamespace('xsi', self::XML_NAMESPACE_XSI);
                foreach ($this->_protectedExpressions as $key => $xpr) {
                    if ($value->xpath($xpr)) {
                        $this->error($key);
                    }
                }
                $errors = $this->getMessages();
                if (!empty($errors)) {
                    return false;
                }
            }
        } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
            $this->error(self::XML_INVALID, $e->getMessage());
            throw $e;
        } catch (ValidationSchemaException $e) {
            $this->error(self::XSD_INVALID, $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->error(self::XML_INVALID);
            throw $e;
        }
        return true;
    }
}
