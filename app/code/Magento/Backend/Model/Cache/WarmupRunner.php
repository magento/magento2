<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Cache;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends HTTP GET requests to warm full-page cache after CLI cache operations.
 */
class WarmupRunner
{
    private const XML_PATH_URLS = 'dev/cache_warmup/urls';

    private const XML_PATH_TIMEOUT = 'dev/cache_warmup/timeout';

    /**
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Curl $curl,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Run configured warmup URLs.
     *
     * @param OutputInterface $output
     * @return int Number of successfully warmed URLs (HTTP 2xx/3xx)
     */
    public function run(OutputInterface $output): int
    {
        $urls = $this->resolveUrls();
        $toWarm = [];
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url !== '') {
                $toWarm[] = $url;
            }
        }
        if ($toWarm === []) {
            $output->writeln(
                '<comment>No warmup URLs configured. Set Stores > Configuration > Advanced > Developer > '
                . 'CLI Cache Warmup > Warmup URLs (one per line), or leave empty to use the storefront base URL.'
                . '</comment>'
            );
            return 0;
        }

        $total = count($toWarm);
        $output->writeln(sprintf('<comment>Cache warmup: in progress (%d stage(s))…</comment>', $total));

        $timeout = (int) $this->scopeConfig->getValue(self::XML_PATH_TIMEOUT);
        if ($timeout < 1) {
            $timeout = 30;
        }

        $this->curl->setOptions([CURLOPT_TIMEOUT => $timeout, CURLOPT_FOLLOWLOCATION => true]);
        $ok = 0;

        foreach ($toWarm as $index => $url) {
            $stage = $index + 1;
            $output->writeln(sprintf('<comment>Stage %d/%d:</comment> %s', $stage, $total, $url));
            try {
                $this->curl->get($url);
                $status = (int) $this->curl->getStatus();
                if ($status >= 200 && $status < 400) {
                    $output->writeln(sprintf('<info>[%d]</info> %s', $status, $url));
                    $ok++;
                } else {
                    $output->writeln(sprintf('<error>[%d]</error> %s', $status, $url));
                }
            } catch (\Throwable $e) {
                $output->writeln(sprintf('<error>%s</error> — %s', $url, $e->getMessage()));
            }
        }

        $output->writeln(sprintf('<info>Cache warmup finished: %d/%d URLs OK.</info>', $ok, $total));

        return $ok;
    }

    /**
     * Resolve URLs from configuration or default storefront base URL.
     *
     * @return string[]
     */
    private function resolveUrls(): array
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_PATH_URLS, ScopeInterface::SCOPE_STORE);
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $configured = array_values(array_filter(array_map('trim', $lines), 'strlen'));

        if ($configured !== []) {
            return $configured;
        }

        try {
            $base = $this->storeManager->getStore()->getBaseUrl();
            return $base !== '' ? [rtrim($base, '/') . '/'] : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
