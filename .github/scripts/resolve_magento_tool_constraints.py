#!/usr/bin/env python3
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
"""
Resolve PHP version + magento/magento2 composer.json the same way as pr-quality-gates.yml.

Used in GitHub Actions (writes GITHUB_OUTPUT and /tmp/magento2-composer.json) and locally:

  REPO_NAME=magento2b2b BASE_REF=2.4.8-p3 \\
    python3 .github/scripts/resolve_magento_tool_constraints.py --print-summary

See emulate_ci_tools_locally.sh for a full local QA-tools install.
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
import urllib.request
from pathlib import Path
from typing import Any


def parse_php_constraint(text: str) -> str:
    matches = re.findall(r"\b8\.(\d+)\b", text or "")
    if not matches:
        return ""
    highest_minor = max(int(m) for m in matches)
    return f"8.{highest_minor}"


def load_json_url(url: str) -> Any:
    with urllib.request.urlopen(url) as resp:
        return json.load(resp)


def read_pkg_constraint(data: dict[str, Any], pkg: str) -> str:
    req = data.get("require") or {}
    dev = data.get("require-dev") or {}
    v = req.get(pkg) or dev.get(pkg)
    return v if isinstance(v, str) else ""


def resolve(
    repo_name: str,
    base_ref: str,
    fallback_php: str,
) -> tuple[str, str, dict[str, Any] | None, str]:
    """
    Returns (php_version, source_ref, composer_json_data, raw_php_constraint_or_empty).
    """
    # Normalize known repo-name aliases used across forks/exceptions.
    repo_alias_map = {
        "magento2inventory": "inventory",
    }
    normalized_repo_name = repo_alias_map.get(repo_name, repo_name)

    repo_package_map = {
        "magento2ce": "magento/magento2-base",
        "magento2ee": "magento/magento2-ee-base",
        "magento2b2b": "magento/magento2-b2b-base",
        "inventory": "magento/inventory-metapackage",
        "magento2-page-builder": "magento/module-page-builder",
        "magento2-page-builder-ee": "magento/page-builder-commerce",
        "security-package": "magento/security-package",
        "security-package-ee": "magento/security-package-ee",
    }

    selected_release = ""
    package_key = repo_package_map.get(normalized_repo_name, "")
    base_ref = (base_ref or "").strip()

    def base_ref_candidates(ref: str) -> list[str]:
        """
        Build lookup candidates for branch-like refs.
        Examples:
        - 2.4.8-p3-develop -> [2.4.8-p3-develop, 2.4.8-p3]
        - 1.2.6-p2-release -> [1.2.6-p2-release, 1.2.6-p2]
        """
        if not ref:
            return []
        out = [ref]
        if ref.endswith("-develop"):
            trimmed = ref[: -len("-develop")]
            if trimmed:
                out.append(trimmed)
        if ref.endswith("-release"):
            trimmed = ref[: -len("-release")]
            if trimmed:
                out.append(trimmed)
        return list(dict.fromkeys(out))

    selected_component_version = ""
    try:
        releases = load_json_url(
            "https://raw.githubusercontent.com/magento/quality-patches/master/magento_releases.json"
        )
        if package_key:
            release_lookup_refs = base_ref_candidates(base_ref)

            for candidate_ref in release_lookup_refs:
                if candidate_ref in releases:
                    selected_release = candidate_ref
                    break

            if not selected_release and release_lookup_refs:

                def sort_key(v: str) -> tuple:
                    m = re.match(r"^(\d+\.\d+\.\d+)(?:-p(\d+))?$", v)
                    if not m:
                        return (v, -1)
                    return (m.group(1), int(m.group(2) or 0))

                for candidate_ref in release_lookup_refs:
                    release_candidates = [
                        key
                        for key in releases
                        if key == candidate_ref or key.startswith(f"{candidate_ref}-p")
                    ]
                    if release_candidates:
                        selected_release = sorted(release_candidates, key=sort_key)[-1]
                        break

            if selected_release:
                selected_component_version = (
                    releases.get(selected_release, {}).get(package_key, "") or ""
                )
    except Exception as exc:
        print(f"Could not read magento_releases.json: {exc}", file=sys.stderr)

    if selected_release:
        print(f"Resolved Magento release from quality-patches metadata: {selected_release}")
        if selected_component_version:
            print(f"Resolved component version for {package_key}: {selected_component_version}")
    else:
        print(
            f"No release mapping found for repo '{repo_name}' and base ref '{base_ref}'."
        )
        print("Falling back to develop-branch context: 2.4-develop / develop")

    composer_refs: list[str] = []
    if selected_release:
        composer_refs.append(selected_release)
    composer_refs.extend(["2.4-develop", "develop"])

    constraint = ""
    source_ref = ""
    composer_json_data: dict[str, Any] | None = None

    for ref in composer_refs:
        try:
            with urllib.request.urlopen(
                f"https://raw.githubusercontent.com/magento/magento2/{ref}/composer.json"
            ) as resp:
                data = json.load(resp)
        except Exception:
            continue
        candidate = (
            data.get("config", {}).get("platform", {}).get("php")
            or data.get("require", {}).get("php")
            or ""
        )
        if candidate:
            constraint = candidate
            source_ref = ref
            composer_json_data = data
            break

    if composer_json_data is None:
        for ref in composer_refs:
            try:
                with urllib.request.urlopen(
                    f"https://raw.githubusercontent.com/magento/magento2/{ref}/composer.json"
                ) as resp:
                    data = json.load(resp)
                composer_json_data = data
                source_ref = ref
                print(
                    f"Loaded magento/magento2 composer.json from ref '{ref}' "
                    "(no PHP platform/require match)."
                )
                break
            except Exception:
                continue

    if composer_json_data is None:
        try:
            with urllib.request.urlopen(
                "https://api.github.com/repos/magento/magento2"
            ) as resp:
                meta = json.load(resp)
            default_branch = meta.get("default_branch") or "2.4-develop"
            with urllib.request.urlopen(
                f"https://raw.githubusercontent.com/magento/magento2/{default_branch}/composer.json"
            ) as resp:
                composer_json_data = json.load(resp)
            source_ref = default_branch
            print(
                f"Fell back to magento/magento2 default branch '{default_branch}' for composer.json."
            )
        except Exception as exc:
            print(f"Could not load magento/magento2 composer.json: {exc}", file=sys.stderr)

    if constraint:
        version = parse_php_constraint(constraint) or fallback_php
        print(f"Resolved PHP constraint from magento/magento2 ref '{source_ref}': {constraint}")
    else:
        version = fallback_php
        print(
            "No usable PHP constraint found from selected tag or fallback refs, "
            f"falling back to PHP {fallback_php}"
        )

    print(f"Selected PHP version: {version}")
    return version, source_ref, composer_json_data, constraint


def merge_tool_constraints(
    magento_data: dict[str, Any] | None,
    mcs_composer_path: Path | None,
) -> tuple[str, str]:
    phpmd_c = ""
    stan_c = ""
    if mcs_composer_path and mcs_composer_path.is_file():
        try:
            mcs = json.loads(mcs_composer_path.read_text(encoding="utf-8"))
            phpmd_c = read_pkg_constraint(mcs, "phpmd/phpmd")
            stan_c = read_pkg_constraint(mcs, "phpstan/phpstan")
        except Exception as exc:
            print(f"Could not read {mcs_composer_path}: {exc}", file=sys.stderr)
    if magento_data:
        if not phpmd_c:
            phpmd_c = read_pkg_constraint(magento_data, "phpmd/phpmd")
        if not stan_c:
            stan_c = read_pkg_constraint(magento_data, "phpstan/phpstan")
    return phpmd_c, stan_c


def main() -> int:
    p = argparse.ArgumentParser(description=__doc__)
    p.add_argument(
        "--repo-name",
        default=os.environ.get("REPO_NAME", ""),
        help="GitHub repository name (e.g. magento2b2b). Env: REPO_NAME",
    )
    p.add_argument(
        "--base-ref",
        default=os.environ.get("BASE_REF", ""),
        help="PR base branch / release ref (e.g. 2.4.8-p3). Env: BASE_REF",
    )
    p.add_argument(
        "--output",
        default=os.environ.get("MAGENTO_COMPOSER_JSON", "/tmp/magento2-composer.json"),
        help="Where to write magento/magento2 composer.json",
    )
    p.add_argument(
        "--fallback-php",
        default="8.3",
        help="PHP minor fallback when constraint cannot be parsed",
    )
    p.add_argument(
        "--mcs-composer",
        type=Path,
        default=None,
        help="Optional path to mcs/composer.json for tool constraint merge",
    )
    p.add_argument(
        "--print-summary",
        action="store_true",
        help="Print phpmd/phpstan constraints after resolution",
    )
    args = p.parse_args()

    php_version, source_ref, composer_json_data, _ = resolve(
        args.repo_name, args.base_ref, args.fallback_php
    )

    out_path = Path(args.output)
    if composer_json_data is not None:
        out_path.parent.mkdir(parents=True, exist_ok=True)
        out_path.write_text(
            json.dumps(composer_json_data, indent=2) + "\n", encoding="utf-8"
        )
        print(
            f"Wrote {out_path} from magento/magento2 ref '{source_ref}' (for PHPMD/PHPStan pins)."
        )
    else:
        print("ERROR: No composer.json data to write.", file=sys.stderr)
        return 1

    github_output = os.environ.get("GITHUB_OUTPUT")
    if github_output:
        outp = Path(github_output)
        with outp.open("a", encoding="utf-8") as fh:
            fh.write(f"php_version={php_version}\n")
            if source_ref:
                fh.write(f"magento_composer_ref={source_ref}\n")

    mcs_path = args.mcs_composer
    if mcs_path is None:
        env_mcs = os.environ.get("MCS_COMPOSER")
        if env_mcs:
            mcs_path = Path(env_mcs)

    if args.print_summary or not github_output:
        phpmd_c, stan_c = merge_tool_constraints(composer_json_data, mcs_path)
        print("")
        print("--- Tool constraints (mcs first, then magento2 composer) ---")
        print(f"magento_composer_ref={source_ref}")
        print(f"php_version={php_version}")
        print(f"phpmd_constraint={phpmd_c or '(missing)'}")
        print(f"phpstan_constraint={stan_c or '(missing)'}")

    return 0


if __name__ == "__main__":
    sys.exit(main())
