<?php declare(strict_types=1);

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/**
 * @codingStandardsIgnoreStart
 */
class ConfigDomMock
{
    /**
     * @var string|null
     */
    private $initialContents;
    
    /**
     * @var string
     */
    private $typeAttribute;
    
    /**
     * @param null|string $initialContents
     * @param mixed $validationState
     * @param array $idAttributes
     * @param string $typeAttribute
     * @param mixed $perFileSchema
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($initialContents, $validationState, $idAttributes, $typeAttribute, $perFileSchema)
    {
        $this->initialContents = $initialContents;
        $this->typeAttribute = $typeAttribute;
    }

    /**
     * @param $schemaFile
     * @param $errors
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($schemaFile, $errors)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDom()
    {
        return 'reader dom result';
    }
}
