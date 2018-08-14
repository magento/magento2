<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\App;

class Filesystem extends \Magento\Framework\Filesystem
{
    /**
     * Overridden paths
     *
     * @var string[]
     */
    private $paths = [];

    /**
     * {@inheritdoc}
     */
    protected function getDirPath($code)
    {
        return $this->getOverriddenPath($code, parent::getDirPath($code));
    }

    /**
     * Overrides a path to directory for testing purposes
     *
     * @param string $code
     * @param string $value
     * @return void
     */
    public function overridePath($code, $value)
    {
        $this->paths[$code] = str_replace('\\', '/', $value);
        unset($this->readInstances[$code]);
        unset($this->writeInstances[$code]);
    }

    /**
     * Looks up an overridden directory path
     *
     * @param string $code
     * @param string $original
     * @return string
     */
    private function getOverriddenPath($code, $original)
    {
        if (array_key_exists($code, $this->paths)) {
            return $this->paths[$code];
        }
        return $original;
    }
}
