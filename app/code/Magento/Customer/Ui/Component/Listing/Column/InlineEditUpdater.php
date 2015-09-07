<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentInterface;

class InlineEditUpdater
{
    /** @var ValidationRules  */
    protected $validationRules;

    /**
     * List of frontend inputs that should be editable in grid
     *
     * @var array
     */
    protected $editableFields = [
        'text',
        'boolean',
        'select',
        'date',
    ];

    /**
     * @param ValidationRules $validationRules
     */
    public function __construct(
        ValidationRules $validationRules
    ) {
        $this->validationRules = $validationRules;
    }

    /**
     * Add editor config
     *
     * @param UiComponentInterface $column
     * @param string $frontendInput
     * @param array $validationRules
     * @param bool|false $isRequired
     * @return UiComponentInterface
     */
    public function applyEditing(
        UiComponentInterface $column,
        $frontendInput,
        array $validationRules,
        $isRequired = false
    ) {
        if (in_array($frontendInput, $this->editableFields)) {
            $config = $column->getConfiguration();
            $config['editor'] = [
                'editorType' => $config['dataType']
            ];
            $validationRules = $this->validationRules->getValidationRules($isRequired, $validationRules);
            if (!empty($validationRules)) {
                $config['editor']['validation'] = $validationRules;
            }
            $column->setData('config', $config);
        }
        return $column;
    }
}
