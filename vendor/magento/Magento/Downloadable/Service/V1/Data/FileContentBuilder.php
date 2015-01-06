<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
