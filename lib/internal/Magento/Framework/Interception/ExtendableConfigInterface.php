<?php
/**
 * Interception config. Tells whether plugins have been added for type.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;


/**
 * Interface \Magento\Framework\Interception\ExtendableConfigInterface
 *
 */
interface ExtendableConfigInterface extends \Magento\Framework\Interception\ConfigInterface
{

    /**
     * Regenerates interception config using latest DI configuration
     *
     * @param array $classDefinitions
     * @param string $areaCode
     * @return void
     */
    public function extend(array $classDefinitions, string $areaCode);

}
