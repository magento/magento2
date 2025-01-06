<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Config;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\Exception\ValidatorException;
use Magento\Theme\Model\DesignConfigRepository;

class PathValidator extends \Magento\Config\Model\Config\PathValidator
{
    /**
     * @param Structure $structure
     * @param DesignConfigRepository $designConfigRepository
     */
    public function __construct(
        private readonly Structure $structure,
        private readonly DesignConfigRepository $designConfigRepository
    ) {
        parent::__construct($structure);
    }

    /**
     * @inheritdoc
     */
    public function validate($path)
    {
        if (stripos($path, 'design/') !== 0) {
            return parent::validate($path);
        }

        return $this->validateDesignPath($path);
    }

    /**
     * Get design configuration field paths
     *
     * @return array
     */
    private function getDesignFieldPaths(): array
    {
        $designConfig = $this->designConfigRepository->getByScope('default', null);
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        $data = [];
        foreach ($fieldsData as $fieldData) {
            $data[$fieldData->getFieldConfig()['path']] = [$fieldData->getFieldConfig()['path']];
        }
        return $data;
    }

    /**
     * Validate design path configurations
     *
     * @param string $path
     * @return bool
     * @throws ValidatorException
     */
    private function validateDesignPath(string $path): bool
    {
        $element = $this->structure->getElementByConfigPath($path);
        if ($element instanceof Structure\Element\Field && $element->getConfigPath()) {
            $path = $element->getConfigPath();
        }

        $allPaths = array_merge($this->structure->getFieldPaths(), $this->getDesignFieldPaths());

        if (!array_key_exists($path, $allPaths)) {
            throw new ValidatorException(__('The "%1" path doesn\'t exist. Verify and try again.', $path));
        }
        return true;
    }
}
