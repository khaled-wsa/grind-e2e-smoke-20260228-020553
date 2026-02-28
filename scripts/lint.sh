#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${repo_root}"

mapfile -t php_files < <(git ls-files '*.php' | LC_ALL=C sort)

if [ "${#php_files[@]}" -eq 0 ]; then
    echo "No PHP files found."
    exit 0
fi

for file in "${php_files[@]}"; do
    php -l "${file}" > /dev/null
done

echo "Linted ${#php_files[@]} PHP files successfully."
