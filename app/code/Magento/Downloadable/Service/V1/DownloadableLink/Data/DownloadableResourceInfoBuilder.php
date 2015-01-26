<?php
/**
 * Downloadable Link Builder
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Framework\Api\ExtensibleObjectBuilder;

class DownloadableResourceInfoBuilder extends ExtensibleObjectBuilder
{
    /**
     * Set file path
     *
     * @param string|null $value
     * @return $this
     */
    public function setFile($value)
    {
        return $this->_set(DownloadableResourceInfo::FILE, $value);
    }

    /**
     * Set URL
     *
     * @param string|null $value
     * @return $this
     */
    public function setUrl($value)
    {
        return $this->_set(DownloadableResourceInfo::URL, $value);
    }

    /**
     * Set value type
     *
     * @param string $value
     * @throws \Magento\Framework\Exception\InputException
     * @return $this
     */
    public function setType($value)
    {
        $allowedValues = ['url', 'file'];
        if (!in_array($value, $allowedValues)) {
            $values = '\'' . implode('\' and \'', $allowedValues) . '\'';
            throw new \Magento\Framework\Exception\InputException('Allowed type values are ' . $values);
        }
        return $this->_set(DownloadableResourceInfo::TYPE, $value);
    }
}
