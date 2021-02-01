<?php
namespace Magento\RemoteStorage\Driver\Adapter;

interface MetadataProviderInterface
{
    public function getMetadata(string $path): array;
}
