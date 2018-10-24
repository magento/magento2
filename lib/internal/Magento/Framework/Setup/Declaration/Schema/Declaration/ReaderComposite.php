<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Declaration;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ReaderInterface;

/**
 * Read schema from different places: XML, csv, etc.
 * You can add one more reader from di.xml.
 * Note: that schema from your reader will not be validated through XSD.
 */
class ReaderComposite implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ReaderInterface[] $readers
     */
    public function __construct(DeploymentConfig $deploymentConfig, array $readers = [])
    {
        $this->readers = $readers;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function read($scope = null)
    {
        $schema = ['table' => []];
        foreach ($this->readers as $reader) {
            $schema = array_replace_recursive($schema, $reader->read($scope));
        }

        return $schema;
    }
}
