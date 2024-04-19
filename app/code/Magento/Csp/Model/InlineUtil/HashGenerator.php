<?php
declare(strict_types=1);

namespace Magento\Csp\Model\InlineUtil;

class HashGenerator
{
    /**
     * Generate fetch policy hash for some content.
     *
     * @param string $content
     * @return array Hash data to insert into a FetchPolicy.
     */
    public function generateHashValue(string $content): array
    {
        return [base64_encode(hash('sha256', $content, true)) => 'sha256'];
    }
}
