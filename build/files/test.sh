#!/usr/bin/env bash
set -euo pipefail

echo "=== Setup unit tests and run them ==="

SHOP_DIR="/var/www/html/"
SHOP_WEB_DIR="${SHOP_DIR}/source"
VENDOR_DIR="${SHOP_WEB_DIR}/modules/bestit"
MODULE_BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"
TEST_SUITE="${MODULE_BASE_DIR}/tests/"

if [[ -f "${SHOP_WEB_DIR}/vendor/bin/runtests" ]]; then
        ${SHOP_WEB_DIR}/vendor/bin/runtests
    else
        unset TRAVIS_ERROR_LEVEL
        ${SHOP_DIR}/vendor/bin/runtests
fi