<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\Config;

/**
 * Transforms Analytics configuration data.
 */
class Mapper
{
    /**
     * Transforms Analytics configuration data.
     *
     * @param array $configData
     * @return array $files
     * $files = [
     *    'file_name' => [
     *        'name' => 'file_name',
     *        'providers' => [
     *            'reportProvider' => [
     *                'name' => 'report_provider_name',
     *                'class' => 'Magento\Analytics\ReportXml\ReportProvider',
     *                'parameters' =>[
     *                    'name' => 'report_name',
     *                ],
     *            ],
     *            'customProvider' => [
     *                'name' => 'custom_provider_name',
     *                'class' => 'Magento\Analytics\Model\CustomProvider',
     *            ],
     *        ],
     *    ]
     * ];
     */
    public function execute($configData)
    {
        if (!isset($configData['config'][0]['file'])) {
            return [];
        }

        $files = [];
        foreach ($configData['config'][0]['file'] as $fileData) {
            /** just one set of providers is allowed by xsd */
            $providers = reset($fileData['providers']);
            foreach ($providers as $providerType => $providerDataSet) {
                /** just one set of provider data is allowed by xsd */
                $providerData = reset($providerDataSet);
                /** just one set of parameters is allowed by xsd */
                $providerData['parameters'] = !empty($providerData['parameters'])
                    ? reset($providerData['parameters'])
                    : [];
                $providerData['parameters'] = array_map(
                    'reset',
                    $providerData['parameters']
                );
                $providers[$providerType] = $providerData;
            }
            $files[$fileData['name']] = $fileData;
            $files[$fileData['name']]['providers'] = $providers;
        }
        return $files;
    }
}
