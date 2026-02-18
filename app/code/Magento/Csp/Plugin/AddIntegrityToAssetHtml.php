<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\State;
use Magento\Deploy\Package\Package;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Request\Http;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;
use Magento\Framework\Escaper;

/**
 * Plugin to add integrity attributes to merged file asset script tags during rendering
 */
class AddIntegrityToAssetHtml
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var SriEnabledActions
     */
    private SriEnabledActions $action;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param State $state
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     * @param Http $request
     * @param SriEnabledActions $action
     * @param Escaper $escaper
     */
    public function __construct(
        State $state,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool,
        Http $request,
        SriEnabledActions $action,
        Escaper $escaper
    ) {
        $this->state = $state;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
        $this->request = $request;
        $this->action = $action;
        $this->escaper = $escaper;
    }

    /**
     * Add integrity attribute to rendered asset HTML for payment pages
     *
     * @param Renderer $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderHeadAssets(
        Renderer $subject,
        string $result
    ): string {
        if (!$this->action->isPaymentPageAction($this->request->getFullActionName())) {
            return $result;
        }

        $result = preg_replace_callback(
            '/<script\s+([^>]*)src="([^"]*\/merged\/[^"]+)"([^>]*)><\/script>/i',
            function ($matches) {
                $beforeSrc = $matches[1];
                $srcUrl = $matches[2];
                $afterSrc = $matches[3];

                $fullAttrs = $beforeSrc . $afterSrc;
                if (stripos($fullAttrs, 'integrity=') !== false) {
                    return $matches[0];
                }

                if (preg_match('#/static/(?:version\d+/)?(.+)$#', $srcUrl, $pathMatches)) {
                    $assetPath = $pathMatches[1];

                    $integrity = $this->getIntegrityForPath($assetPath);

                    if ($integrity) {
                        $afterSrc = ' integrity="' . $this->escaper->escapeHtmlAttr($integrity) . '"' .
                                   ' crossorigin="anonymous"' . $afterSrc;
                    }
                }

                return '<script ' . $beforeSrc . 'src="' . $srcUrl . '"' . $afterSrc . '></script>';
            },
            $result
        );

        return $result;
    }

    /**
     * Get integrity hash for a given asset path
     *
     * @param string $assetPath
     * @return string|null
     */
    private function getIntegrityForPath(string $assetPath): ?string
    {
        $integrityRepository = $this->integrityRepositoryPool->get(Package::BASE_AREA);
        $integrity = $integrityRepository->getByPath($assetPath);

        if (!$integrity) {
            try {
                $integrityRepository = $this->integrityRepositoryPool->get(
                    $this->state->getAreaCode()
                );
                $integrity = $integrityRepository->getByPath($assetPath);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $integrity?->getHash();
    }
}
