<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\Data;

use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class FileContentBuilder extends ExtensibleObjectBuilder
{
    /**
     * Set data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     */
    public function setData($data)
    {
        return $this->_set(FileContent::DATA, $data);
    }

    /**
     * Set file name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->_set(FileContent::NAME, $name);
    }
}
