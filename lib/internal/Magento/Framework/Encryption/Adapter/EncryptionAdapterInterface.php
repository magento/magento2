<?php

namespace Magento\Framework\Encryption\Adapter;

interface EncryptionAdapterInterface
{
    /**
     * @param $data
     * @return string
     */
    public function encrypt($data);

    /**
     * @param string $data
     * @return string
     */
    public function decrypt($data);
}
