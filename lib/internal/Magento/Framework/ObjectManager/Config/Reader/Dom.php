<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\ObjectManager\Config\Reader;

class Dom extends \Magento\Framework\Config\Reader\Filesystem implements
    \Magento\Framework\ObjectManager\ResetAfterRequestInterface
{
    /**
     * Name of an attribute that stands for data type of node values
     */
    const TYPE_ATTRIBUTE = 'xsi:type';

    /**
     * @var array
     */
    protected $_idAttributes = [
        '/config/preference' => 'for',
        '/config/(type|virtualType)' => 'name',
        '/config/(type|virtualType)/plugin' => 'name',
        '/config/(type|virtualType)/arguments/argument' => 'name',
        '/config/(type|virtualType)/arguments/argument(/item)+' => 'name',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\ObjectManager\Config\Mapper\Dom $converter
     * @param \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\ObjectManager\Config\Mapper\Dom $converter,
        \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'di.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    /**
     * Parsed configuration per scope, memoized for the lifetime of this reader.
     *
     * Reading a scope re-parses and DOM-merges every di.xml that contributes to it (~250 files
     * and ~12k nodes for 'global'), and several consumers ask the same reader for the same scope
     * during one setup:di:compile. The cache is per instance on purpose: a result depends on this
     * reader's file resolver, merge rules, schema and validation state, so two differently
     * configured readers must never see each other's results.
     *
     * @var array
     */
    private $scopeCache = [];

    /**
     * Read configuration for the given scope, parsing each scope at most once per reader.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        // Normalise exactly as the parent does, so read() and read($defaultScope) share an entry.
        $scope = $scope ?: $this->_defaultScope;
        if (!array_key_exists($scope, $this->scopeCache)) {
            $this->scopeCache[$scope] = parent::read($scope);
        }

        return $this->scopeCache[$scope];
    }

    /**
     * @inheritdoc
     *
     * The parsed scopes are held for the lifetime of this reader, which is shared and long-lived:
     * app/etc/di.xml wires it into the configuration loader, the interception config and the
     * plugin list. That is fine for a command, but a long-running process should not carry a
     * request's parsed configuration into the next one.
     */
    public function _resetState(): void
    {
        $this->scopeCache = [];
    }

    /**
     * Create and return a config merger instance that takes into account types of arguments
     *
     * {@inheritdoc}
     */
    protected function _createConfigMerger($mergerClass, $initialContents)
    {
        return new $mergerClass(
            $initialContents,
            $this->validationState,
            $this->_idAttributes,
            self::TYPE_ATTRIBUTE,
            $this->_perFileSchema
        );
    }
}
