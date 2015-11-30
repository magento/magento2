<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design;

use Magento\Framework\App\Config\ValueFactory;

class BackendModelFactory extends ValueFactory
{
    /**
     * @inheritDoc
     */
    public function create(array $data = [])
    {
        $backendModel = isset($data['config']['backend_model'])
            ? $this->_objectManager->create($data['config']['backend_model'])
            : parent::create();

        $backendModelData = [
            'path' => $data['config']['path'],
            'value' => $data['value'],
            'scope' => $data['scope'],
            'scope_id' => $data['scopeId'],
            'field_config' => $data['config'],
        ];
        if (isset($data['extendedConfig'][$data['config']['path']])) {
            $backendModelData['config_id'] = $data['extendedConfig'][$data['config']['path']]['config_id'];
        }

        $backendModel->addData($backendModelData);

        return $backendModel;
    }
}
