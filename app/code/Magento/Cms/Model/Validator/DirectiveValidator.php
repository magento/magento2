<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Validator;

class DirectiveValidator
{
    /**
     * Verify block content
     *
     * @param string $html
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isValid(string $html): bool
    {
        if (preg_match('#<(pre|code)\b[^>]*>.*?{{\s*block\b.*?}}.*?</\1>#si', $html)) {
            return false;
        }

        if (!preg_match_all('/{{\s*block\b(.*?)}}/si', $html, $matches, PREG_SET_ORDER)) {
            return true;
        }

        foreach ($matches as $m) {
            $raw = $m[1] ?? '';

            if ($raw !== strip_tags($raw)) {
                return false;
            }

            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $body = html_entity_decode($raw, ENT_QUOTES);
            $params = $this->parseParams($body);

            if (!isset($params['class']) && !isset($params['id'])) {
                return false;
            }

            if (isset($params['class'])) {
                $class = trim((string)$params['class']);
                if (!preg_match('/^\\\\?[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $class)) {
                    return false;
                }
            }

            if (isset($params['name'])) {
                $name = (string)$params['name'];
                if (!preg_match('/^[A-Za-z0-9_.-]+$/', $name)) {
                    return false;
                }
            }

            if (isset($params['template'])) {
                $template = (string)$params['template'];
                if (!preg_match('/^[A-Za-z0-9_]+(?:_[A-Za-z0-9_]+)*::[A-Za-z0-9_.\-\/]+$/', $template)) {
                    return false;
                }
            }

            if (isset($params['id'])) {
                $id = (string)$params['id'];
                if (!preg_match('/^[A-Za-z0-9_\-]+$/', $id)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Parses a parameter string into an associative array.
     *
     * @param string $value
     * @return array
     */
    private function parseParams(string $value): array
    {
        $params = [];
        if (preg_match_all('/(\w+)\s*=\s*("([^"]*)"|\'([^\']*)\'|(\S+))/u', $value, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $p) {
                $params[$p[1]] = $p[3] ?? ($p[4] ?? $p[5] ?? '');
            }
        }
        return $params;
    }
}
