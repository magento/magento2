<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Api\Config\Reader;

/**
 * Config file resolver for extension_attributes.xml files, which reads configs defined in tests.
 *
 * It is necessary because these configs are used during extension classes generation. And thus it is impossible
 * to add customizations to the configs in concrete test, because respective extension class is already generated
 * and loaded by the PHP. It is impossible to reload definition of the class, which is already loaded.
 */
class FileResolver extends \Magento\Framework\App\Config\FileResolver
{
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        // TODO: Merge parent result with a list of configs located at
        // TODO: integration/testsuite/Magento/*/etc/extension_attributes.xml
        // TODO: and integration/testsuite/Magento/Framework/*/etc/extension_attributes.xml
        // TODO: Result can be an array of file paths according to the interface,
        // TODO: not necessarily file iterator should be created
        return parent::get($filename, $scope);
    }
}
