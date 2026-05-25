#!/usr/bin/env python3
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
# .github/scripts/check_copyright.py - Magento Copyright Header Checker

import os
import sys
import re
import click
from pathlib import Path
from typing import List, Tuple
from datetime import datetime
import subprocess

def _resolve_git_root() -> str:
    result = subprocess.run(
        ['git', 'rev-parse', '--show-toplevel'],
        capture_output=True, text=True, check=False
    )
    return result.stdout.strip() if result.returncode == 0 else '.'

_GIT_ROOT = _resolve_git_root()

# File extensions and their comment styles.
# Aligned with the PHP update_copyrights.php updater which supports:
# PHP, PHTML, HTML, JS, XML, XSD, CSS, LESS
COMMENT_STYLES = {
    # PHP files
    '.php': ('<?php\n/**\n * ', ' * ', ' */\n'),
    '.phtml': ('<?php\n/**\n * ', ' * ', ' */\n?>\n'),

    # Frontend files
    '.js': ('/**\n * ', ' * ', ' */\n'),
    '.css': ('/**\n * ', ' * ', ' */\n'),
    '.less': ('/**\n * ', ' * ', ' */\n'),
    '.scss': ('/**\n * ', ' * ', ' */\n'),

    # Configuration files
    '.xml': ('<?xml version="1.0"?>\n<!--\n', ' * ', '\n-->\n'),
    '.xsd': ('<?xml version="1.0"?>\n<!--\n', ' * ', '\n-->\n'),

    # GraphQL schema files
    '.graphqls': ('# ', '# ', '\n'),

    # Markup files
    '.html': ('<!--\n', ' * ', '\n-->'),
}

# Magento-specific exclusion patterns
MAGENTO_EXCLUSIONS = {
    'vendor',           # Composer dependencies
    'var',              # Cache and temporary files
    'generated',        # Auto-generated code
    'pub/static',       # Generated static files
    'pub/media',        # Media files
    # dev/tests is intentionally NOT excluded — Magento test files under
    # dev/tests/*/testsuite/ do need copyright headers.
    'lib/web',          # Third-party web libraries
    'app/etc',          # Configuration files
    'setup/src/Magento/Setup/Fixtures',  # Setup fixtures
    'node_modules',     # Node.js dependencies
    '.git',             # Git files
    'phpserver',        # PHP server files
    'grunt',            # Grunt files
    'dev/tools',        # Development tools
    'dev/build',        # Build files
    'update',           # Update files
    'bin',              # Binary files
}

# Magento module structure patterns
MAGENTO_MODULE_PATTERNS = [
    r'app/code/[^/]+/[^/]+/',  # app/code/Vendor/Module/
    r'app/design/[^/]+/[^/]+/[^/]+/',  # app/design/area/vendor/theme/
]


def _normalize_copyright(text: str) -> str:
    """Normalize copyright text for flexible comparison.

    Strips all comment markers, whitespace and punctuation that varies
    between file types, matching the approach used by the Jenkins
    SanityCopyrightChecker.
    """
    # Remove PHP inline declarations so that '<?php declare(strict_types=1)'
    # and '<?php' normalize identically and the copyright substring check passes.
    text = re.sub(r'\bdeclare\s*\([^)]*\)\s*;?', '', text)
    return re.sub(r'[\s#/\*\-<>!?\[\]]+', '', text).lower()



def _is_third_party_file(file_path: str, content: str) -> bool:
    """Detect third-party files that should skip copyright validation.

    Matches the Jenkins SanityCopyrightChecker logic: if neither the file
    path nor its content mentions 'Magento' or 'Adobe', it is assumed to be
    a third-party library file.  Checking the path ensures that files inside
    Magento module directories (app/code/Magento/...) are never skipped even
    when the file content itself contains no such references.
    """
    return ('Magento' not in file_path and 'Adobe' not in file_path
            and 'Magento' not in content and 'Adobe' not in content)


def get_git_creation_year(file_path: str, debug: bool = False, use_current_year: bool = False,
                          base_sha: str = '') -> int:
    """Get the year when file was first added to git."""

    # In PR mode, check whether the file existed in the base commit.
    # git cat-file -e is a single object lookup — no history traversal.
    # git status --porcelain only catches unstaged/untracked files, which is
    # never the case on CI where the PR branch is already checked out clean.
    if use_current_year and base_sha:
        try:
            result = subprocess.run(
                ['git', 'cat-file', '-e', f'{base_sha}:{file_path}'],
                capture_output=True, cwd=_GIT_ROOT
            )
            if result.returncode != 0:
                # File did not exist in base — it is new in this PR.
                current_year = datetime.now().year
                if debug:
                    click.echo(f"  New file (not in base {base_sha[:8]}): using year {current_year}")
                return current_year
            if debug:
                click.echo(f"  File exists in base {base_sha[:8]}: finding original creation year")
        except Exception:
            pass

    if debug:
        click.echo(f"  Finding git creation year for: {file_path}")

    # Primary: follow renames; fallback: without --follow if rename chain fails
    git_commands = [
        ['git', 'log', '--follow', '--format=%ad', '--date=format:%Y', '--reverse', '--', file_path],
        ['git', 'log',             '--format=%ad', '--date=format:%Y', '--reverse', '--', file_path],
    ]

    for i, cmd in enumerate(git_commands):
        try:
            if debug:
                click.echo(f"  Trying command {i+1}: {' '.join(cmd)}")

            result = subprocess.run(cmd, capture_output=True, text=True, cwd=_GIT_ROOT)

            if result.returncode == 0 and result.stdout.strip():
                years = result.stdout.strip().split('\n')
                if years and years[0]:
                    first_year = int(years[0])
                    if debug:
                        click.echo(f"  Found git creation year: {first_year}")
                    return first_year
        except (subprocess.CalledProcessError, ValueError, IndexError) as e:
            if debug:
                click.echo(f"  Command {i+1} failed: {e}")
            continue

    # Fallback to file modification time
    try:
        stat = os.stat(file_path)
        fallback_year = datetime.fromtimestamp(stat.st_mtime).year
        if debug:
            click.echo(f"  Using file modification time fallback: {fallback_year}")
        return fallback_year
    except Exception:
        pass

    current_year = datetime.now().year
    if debug:
        click.echo(f"  Using current year fallback: {current_year}")
    return current_year


def load_template(template_path: str) -> str:
    """Load the copyright template."""
    try:
        with open(template_path, 'r', encoding='utf-8') as f:
            return f.read().strip()
    except FileNotFoundError:
        click.echo(f"Template file not found: {template_path}", err=True)
        sys.exit(1)


def format_copyright_header(template: str, file_path: str, comment_style: Tuple[str, str, str],
                            year: str, debug: bool = False) -> str:
    """Format copyright header for specific file type using the given year."""
    start_comment, line_comment, end_comment = comment_style

    formatted_template = template.replace('{{YEAR}}', year)

    if debug:
        click.echo(f"  File: {file_path} -> Year: {year}")

    file_ext = Path(file_path).suffix.lower()

    # If template already contains proper comment format (like Adobe templates), use as-is
    if formatted_template.strip().startswith('/**') and formatted_template.strip().endswith('*/'):
        if file_ext == '.php':
            return f"<?php\n{formatted_template}\n"
        elif file_ext == '.phtml':
            return f"<?php\n{formatted_template}\n?>\n"
        elif file_ext in ['.xml', '.xsd']:
            content = formatted_template.strip()[3:-2].strip()
            lines = content.split('\n')
            result = ['<?xml version="1.0"?>', '<!--']
            for line in lines:
                line = line.strip()
                if line.startswith('*'):
                    line = line[1:].strip()
                if line:
                    result.append(f' * {line}')
                else:
                    result.append(' *')
            result.append(' -->')
            return '\n'.join(result) + '\n'
        elif file_ext == '.graphqls':
            content = formatted_template.strip()[3:-2].strip()
            lines = content.split('\n')
            result = []
            for line in lines:
                line = line.strip()
                if line.startswith('*'):
                    line = line[1:].strip()
                if line:
                    result.append(f'# {line}')
                else:
                    result.append('#')
            return '\n'.join(result) + '\n'
        elif file_ext == '.html':
            content = formatted_template.strip()[3:-2].strip()
            lines = content.split('\n')
            result = ['<!--']
            for line in lines:
                line = line.strip()
                if line.startswith('*'):
                    line = line[1:].strip()
                if line:
                    result.append(f' * {line}')
                else:
                    result.append(' *')
            result.append(' -->')
            return '\n'.join(result) + '\n'
        else:
            return f"{formatted_template}\n"

    # Format according to file type
    if file_ext in ['.php', '.phtml']:
        lines = formatted_template.split('\n')
        result = ['<?php', '/**']
        for line in lines:
            if line.strip():
                result.append(f' * {line}')
            else:
                result.append(' *')
        result.append(' */')
        if file_ext == '.phtml':
            result.append('?>')
        return '\n'.join(result) + '\n'

    elif file_ext in ['.xml', '.xsd']:
        lines = formatted_template.split('\n')
        result = ['<?xml version="1.0"?>', '<!--']
        for line in lines:
            if line.strip():
                result.append(f' * {line}')
            else:
                result.append(' *')
        result.append(' -->')
        return '\n'.join(result) + '\n'

    elif file_ext in ['.js', '.css', '.less', '.scss']:
        lines = formatted_template.split('\n')
        result = ['/**']
        for line in lines:
            if line.strip():
                result.append(f' * {line}')
            else:
                result.append(' *')
        result.append(' */')
        return '\n'.join(result) + '\n'

    elif file_ext == '.graphqls':
        lines = formatted_template.split('\n')
        result = []
        for line in lines:
            if line.strip():
                result.append(f'# {line}')
            else:
                result.append('#')
        return '\n'.join(result) + '\n'

    elif file_ext in ['.html', '.md']:
        lines = formatted_template.split('\n')
        result = ['<!--']
        for line in lines:
            if line.strip():
                result.append(f' * {line}')
            else:
                result.append(' *')
        result.append(' -->')
        return '\n'.join(result) + '\n'

    return formatted_template + '\n'


def extract_existing_copyright(content: str, comment_style: Tuple[str, str, str]) -> Tuple[str, int]:
    """Extract existing copyright header and return it with the number of lines.

    Uses a state machine with explicit block types so that the end-of-block
    detection is never applied to the wrong comment style.  Three block types:
      C_STYLE  —  /* ... */ or /** ... */
      HTML     —  <!-- ... -->  (including nested /** */ used in XML files)
      HASH     —  consecutive # lines (Python, shell, YAML)
    """
    lines = content.split('\n')

    copyright_patterns = [
        r'\bcopyright\b',
        r'\blicensed?\s+under\b',
        r'\bspdx-license\b',
        r'\ball rights reserved\b',
        r'@copyright',
        r'@license',
        r'\badobe\s+confidential\b',
        r'\bcopyright\s+\d{4}\s+(?:adobe|magento)',
        r'see\s+copying\.txt',
        r'see\s+license',
    ]

    def _has_copyright(text: str) -> bool:
        return any(re.search(p, text.lower()) for p in copyright_patterns)

    C_STYLE = 'c_style'
    HTML    = 'html'
    HASH    = 'hash'

    header_lines: list = []
    line_count = 0
    block_type = None
    found_copyright = False

    for i, line in enumerate(lines):
        stripped = line.strip()

        # Always skip the PHP opening tag and XML declaration on line 0.
        if i == 0 and (stripped.startswith('<?php') or stripped.startswith('<?xml')):
            header_lines.append(line)
            continue

        # ── Not yet inside a block ────────────────────────────────────────
        if block_type is None:
            if stripped.startswith('/**') or stripped.startswith('/*'):
                block_type = C_STYLE
                header_lines.append(line)
                # Handle single-line /* ... */ block.
                if stripped.endswith('*/') and len(stripped) > 2:
                    if _has_copyright(stripped):
                        found_copyright = True
                        line_count = i + 1
                    else:
                        header_lines = []
                    break
                continue

            if stripped.startswith('<!--'):
                block_type = HTML
                header_lines.append(line)
                # Handle single-line <!-- ... --> block.
                if stripped.endswith('-->') and len(stripped) > 4:
                    if _has_copyright(stripped):
                        found_copyright = True
                        line_count = i + 1
                    else:
                        header_lines = []
                    break
                continue

            if stripped.startswith('#'):
                block_type = HASH
                if _has_copyright(stripped):
                    found_copyright = True
                header_lines.append(line)
                line_count = i + 1
                continue

            if stripped == '' and not header_lines:
                continue

            # Non-comment content — nothing more to find.
            break

        # ── Inside a C-style block  /* ... */ ────────────────────────────
        elif block_type == C_STYLE:
            header_lines.append(line)
            line_count = i + 1
            if _has_copyright(stripped):
                found_copyright = True
            if stripped.endswith('*/'):
                if found_copyright:
                    break
                # Non-copyright C-style block; reset and keep looking.
                header_lines = []
                line_count = 0
                block_type = None

        # ── Inside an HTML comment block  <!-- ... --> ────────────────────
        # Lines inside can be free-form text, star-prefixed, or nested /** */.
        # We consume everything until --> regardless of line content.
        elif block_type == HTML:
            header_lines.append(line)
            line_count = i + 1
            if _has_copyright(stripped):
                found_copyright = True
            if stripped.endswith('-->'):
                if found_copyright:
                    break
                # Non-copyright HTML block; reset and keep looking.
                header_lines = []
                line_count = 0
                block_type = None

        # ── Inside a hash-comment block  # lines ─────────────────────────
        elif block_type == HASH:
            if not stripped.startswith('#'):
                # Hash block ended.
                if found_copyright:
                    # The current line is not part of the header.
                    line_count = i
                break
            if _has_copyright(stripped):
                found_copyright = True
            header_lines.append(line)
            line_count = i + 1

    if not found_copyright:
        return '', 0

    return '\n'.join(header_lines), line_count


def is_magento_file(file_path: str) -> bool:
    """Check if file is part of Magento core or a Magento module."""
    for pattern in MAGENTO_MODULE_PATTERNS:
        if re.search(pattern, file_path):
            return True

    magento_files = [
        'registration.php',
        'module.xml',
        'composer.json',
        'requirejs-config.js',
        'web.xml',
    ]

    filename = os.path.basename(file_path)
    return filename in magento_files


def _matches_path_segment(exclusion: str, file_path: str) -> bool:
    """Check if exclusion matches as a path segment (not arbitrary substring)."""
    normalized = f'/{file_path}'
    return f'/{exclusion}/' in normalized or normalized.endswith(f'/{exclusion}')


def should_exclude_file(file_path: str, exclude_list: List[str]) -> bool:
    """Check if file should be excluded from copyright checking."""
    for excl in exclude_list:
        if _matches_path_segment(excl, file_path):
            return True

    for excl in MAGENTO_EXCLUSIONS:
        if _matches_path_segment(excl, file_path):
            return True

    # Exclude test directories outside app/code, but allow dev/tests/*/testsuite/
    # which contains standard Magento test suites that require copyright headers.
    if '/tests/' in file_path.lower() and '/app/code/' not in file_path.lower():
        if not re.search(r'dev/tests/[^/]+/testsuite/', file_path.lower()):
            return True

    filename = os.path.basename(file_path)

    if any(lib in file_path.lower() for lib in ['jquery', 'prototype', 'scriptaculous', 'mage/lib']):
        return True

    if filename.endswith('.min.js') or filename.endswith('.min.css'):
        return True

    return False


def check_file(file_path: str, template: str, fix: bool = False, dry_run: bool = False,
               debug: bool = False, pr_mode: bool = False, base_sha: str = '') -> bool:
    """Check and optionally fix copyright header in a file."""
    ext = Path(file_path).suffix.lower()

    if ext not in COMMENT_STYLES:
        return True

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
    except (UnicodeDecodeError, PermissionError):
        return True

    # Skip third-party files that don't reference Magento/Adobe at all,
    # matching the Jenkins SanityCopyrightChecker behaviour.
    if _is_third_party_file(file_path, content):
        if debug:
            click.echo(f"  Skipping 3rd-party file (no Magento/Adobe reference): {file_path}")
        return True

    comment_style = COMMENT_STYLES[ext]
    existing_header, header_line_count = extract_existing_copyright(content, comment_style)

    # ── Determine the year to validate against ────────────────────────
    # On GitHub Actions we have full git history, so always use the
    # authoritative git creation year.  The Jenkins checker trusts the
    # year in the file because git is not available there, but here we
    # can and should verify it.
    year = str(get_git_creation_year(file_path, debug, use_current_year=pr_mode, base_sha=base_sha))

    expected_header = format_copyright_header(template, file_path, comment_style, year, debug)

    if debug:
        click.echo(f"  Expected header preview:")
        click.echo(f"   {expected_header[:100]}...")
        click.echo(f"  Existing header preview:")
        click.echo(f"   {existing_header[:100]}...")
        click.echo(f"  Year used for comparison: {year}")

    # ── Comparison ────────────────────────────────────────────────────
    # Use normalized comparison (strip all comment markers / whitespace)
    # so minor formatting differences do not cause failures, matching the
    # flexible approach of the Jenkins SanityCopyrightChecker.
    expected_norm = _normalize_copyright(expected_header)
    existing_norm = _normalize_copyright(existing_header)

    # The expected template must appear within the existing header.
    # (Not the reverse -- a nearly-empty header like "php" must not match.)
    if ext in ['.xml', '.xsd']:
        if '/**' in existing_header or '*/' in existing_header:
            if debug:
                click.echo(f"  XML file has incorrect comment style: {file_path}")
            # Fall through to fix / report
        elif expected_norm and expected_norm in existing_norm:
            return True
    else:
        if expected_norm and expected_norm in existing_norm:
            return True

    # ── No match ──────────────────────────────────────────────────────

    # For existing files in PR mode: if the copyright year already in the file
    # is earlier than what git log reports, accept it. This handles files that
    # predate the repository's available git history (e.g. Copyright 2011 when
    # git log only goes back to 2015 due to history truncation or rebase).
    # New files are excluded — they must carry the current year.
    if pr_mode and base_sha and existing_header:
        try:
            cat_result = subprocess.run(
                ['git', 'cat-file', '-e', f'{base_sha}:{file_path}'],
                capture_output=True, cwd=_GIT_ROOT
            )
            if cat_result.returncode == 0:
                existing_year_match = re.search(r'[Cc]opyright\s+(\d{4})', existing_header)
                if existing_year_match and int(existing_year_match.group(1)) < int(year):
                    if debug:
                        click.echo(f'  Accepting copyright year {existing_year_match.group(1)}: '
                                   f'predates git history (git reports {year})')
                    return True
        except Exception:
            pass

    if dry_run:
        click.echo(f"  Would fix: {file_path}")
        return False

    if fix:
        lines = content.split('\n')

        preserved_lines = []
        if lines and (lines[0].startswith('#!') or 'coding:' in lines[0] or 'encoding:' in lines[0]):
            preserved_lines.append(lines[0])

        # When there is no existing copyright (header_line_count == 0) but the
        # file already has a <?php or <?xml opening tag on line 0, skip that line
        # so the generated header (which includes the opening tag) does not
        # produce a duplicate.
        skip = header_line_count
        if skip == 0 and lines and re.match(r'<\?(?:php|xml)\b', lines[0].strip()):
            skip = 1
        remaining_content = '\n'.join(lines[skip:])

        new_content = '\n'.join(preserved_lines)
        if preserved_lines:
            new_content += '\n'
        new_content += expected_header
        if not remaining_content.startswith('\n'):
            new_content += '\n'
        new_content += remaining_content.lstrip('\n')

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)

        click.echo(f"  Fixed: {file_path} (year: {year})")
        return True
    else:
        click.echo(f"  Missing/incorrect copyright: {file_path} (expected year: {year})")
        click.echo(f"  To fix: python3 .github/scripts/check_copyright.py "
                   f"--template .github/scripts/copyright-template.txt "
                   f"--path '{file_path}' --pr-mode --fix")
        return False


@click.command()
@click.option('--template', required=True, help='Path to copyright template file')
@click.option('--fix', is_flag=True, help='Fix copyright headers automatically')
@click.option('--dry-run', is_flag=True, help='Show what would be fixed without making changes')
@click.option('--extensions', default='.php,.phtml,.js,.css,.scss,.less,.xml,.xsd,.html,.graphqls',
              help='Comma-separated list of file extensions to check')
@click.option('--exclude', default='',
              help='Additional comma-separated list of directories to exclude')
@click.option('--magento-only', is_flag=True,
              help='Only check files that are part of Magento modules')
@click.option('--stats', is_flag=True, help='Show detailed statistics')
@click.option('--debug', is_flag=True, help='Show detailed git debugging information')
@click.option('--path', default='.', help='Path to check - can be a file or directory (default: current directory)')
@click.option('--file-list', default='', help='File containing paths to check, one per line (faster than looping --path calls)')
@click.option('--verbose', is_flag=True, help='Show verbose output with detailed information')
@click.option('--pr-mode', is_flag=True, help='PR mode: trust existing year for modified files, current year for new files')
@click.option('--base-sha', default='', help='Base commit SHA for PR mode: used to detect new vs modified files without git log')
def main(template: str, fix: bool, dry_run: bool, extensions: str, exclude: str,
         magento_only: bool, stats: bool, debug: bool, path: str, file_list: str, verbose: bool, pr_mode: bool, base_sha: str):
    """Check copyright headers against a template file (Magento-optimized).

    Template should contain {{YEAR}} placeholder which will be replaced with
    the appropriate year for each file.

    In PR mode (--pr-mode), new files use the current year and existing
    files use their git creation year.  Unlike the Jenkins checker (which
    trusts the year already in the file because git is unavailable there),
    this script verifies the year against git history.

    Examples:

    Check a single file:
    python3 .github/scripts/check_copyright.py --template .github/scripts/copyright-template.txt --path app/code/Magento/MysqlMq/registration.php --dry-run

    Check a directory:
    python3 .github/scripts/check_copyright.py --template .github/scripts/copyright-template.txt --path app/code/Magento/MysqlMq/ --fix

    Check entire project:
    python3 .github/scripts/check_copyright.py --template .github/scripts/copyright-template.txt --magento-only --stats
    """

    if dry_run and fix:
        click.echo("Cannot use --dry-run and --fix together", err=True)
        sys.exit(1)

    template_content = load_template(template)
    extensions_list = [ext.strip() for ext in extensions.split(',')]
    exclude_list = [entry.strip() for entry in exclude.split(',') if entry.strip()]

    if file_list:
        if not os.path.exists(file_list):
            click.echo(f"File list not found: {file_list}", err=True)
            sys.exit(1)
        with open(file_list) as fh:
            paths = [line.strip() for line in fh if line.strip()]
        issues_found = []
        for file_path in paths:
            if not os.path.isfile(file_path):
                continue
            if should_exclude_file(file_path, exclude_list):
                continue
            if not any(file_path.endswith(ext) for ext in extensions_list):
                continue
            if not check_file(file_path, template_content, fix, dry_run, debug, pr_mode, base_sha):
                issues_found.append(file_path)
        if issues_found:
            click.echo(f"\n{len(issues_found)} file(s) have copyright issues")
            sys.exit(1)
        click.echo("All relevant PR files have correct copyright headers")
        return

    if not os.path.exists(path):
        click.echo(f"Path does not exist: {path}", err=True)
        sys.exit(1)

    issues_found = []
    files_checked = 0
    files_skipped = 0
    magento_files = 0
    ext_stats = {}

    is_single_file = os.path.isfile(path)
    show_header = verbose or not is_single_file

    if show_header:
        click.echo(f"Checking copyright headers in: {path}")
        if is_single_file:
            click.echo(f"  Mode: Single file")
        else:
            click.echo(f"  Mode: Directory traversal")
        click.echo(f"  Extensions: {', '.join(extensions_list)}")
        if magento_only:
            click.echo("  Checking Magento modules only")
        if pr_mode:
            click.echo("  PR mode: trusting existing year for modified files")

    if debug:
        click.echo(f"  Debug: Path={path}, is_single_file={is_single_file}")
        click.echo(f"  Debug: magento_only={magento_only}, pr_mode={pr_mode}")
        click.echo(f"  Debug: exclude_list={exclude_list}")

    if is_single_file:
        files_skipped = 0

        if should_exclude_file(path, exclude_list):
            if verbose:
                click.echo(f"  File excluded: {path}")
            files_skipped = 1
        elif magento_only and not is_magento_file(path):
            if verbose:
                click.echo(f"  Not a Magento file: {path}")
            files_skipped = 1
        else:
            file_ext = Path(path).suffix.lower()
            if not any(path.endswith(ext) for ext in extensions_list):
                if verbose:
                    click.echo(f"  Unsupported extension {file_ext}: {path}")
                files_skipped = 1
            else:
                files_checked = 1
                ext_stats[file_ext] = 1
                magento_files = 1 if is_magento_file(path) else 0

                if verbose or debug:
                    click.echo(f"Checking: {path}")

                if not check_file(path, template_content, fix, dry_run, debug, pr_mode, base_sha):
                    issues_found.append(path)
    else:
        if debug:
            click.echo(f"  Debug: Processing directory: {path}")

        total_files_found = 0
        for root, dirs, files in os.walk(path):
            dirs[:] = [d for d in dirs if not should_exclude_file(os.path.join(root, d), exclude_list)]

            for file in files:
                total_files_found += 1
                file_path = os.path.join(root, file)

                if should_exclude_file(file_path, exclude_list):
                    files_skipped += 1
                    continue

                if magento_only and not is_magento_file(file_path):
                    files_skipped += 1
                    continue

                if any(file.endswith(ext) for ext in extensions_list):
                    ext = Path(file).suffix.lower()
                    ext_stats[ext] = ext_stats.get(ext, 0) + 1

                    if is_magento_file(file_path):
                        magento_files += 1

                    files_checked += 1
                    if verbose or debug:
                        click.echo(f"Checking: {file_path}")

                    if not check_file(file_path, template_content, fix, dry_run, debug, pr_mode, base_sha):
                        issues_found.append(file_path)

        if debug:
            click.echo(f"  Debug: Directory walk completed. Total files found: {total_files_found}")
            click.echo(f"  Debug: Files checked: {files_checked}, Files skipped: {files_skipped}")

    if not is_single_file or verbose:
        click.echo(f"\nResults:")
        click.echo(f"  Files checked: {files_checked}")
        click.echo(f"  Files skipped: {files_skipped}")
        click.echo(f"  Magento files: {magento_files}")

        if stats:
            click.echo(f"\nStatistics by extension:")
            for ext, count in sorted(ext_stats.items()):
                click.echo(f"  {ext}: {count} files")

        if issues_found:
            click.echo(f"\n{len(issues_found)} files have copyright issues")
            if dry_run:
                click.echo("Run with --fix to automatically correct them")
            elif not fix:
                click.echo("Run with --fix to automatically correct them")
                click.echo("Run with --dry-run to see what would be changed")
        else:
            if dry_run:
                click.echo("\nAll files would have correct copyright headers")
            else:
                click.echo("\nAll files have correct copyright headers")

    if issues_found:
        sys.exit(1)

if __name__ == "__main__":
    main()
