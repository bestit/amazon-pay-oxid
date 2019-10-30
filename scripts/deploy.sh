#!/usr/bin/env bash
set -euo pipefail

CURRENT_DIR=${PWD}
TEMP_DIR='/tmp/module_build'
VENDOR_DIR='/copy_this/modules/bestit'
BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"
ARCHIVE_NAME="bestitamazonpay4oxid"

if [[ -d ${CURRENT_DIR}/vendor ]]; then
    sudo rm -rf ${CURRENT_DIR}/vendor
fi

sudo chmod -R 777 ${CURRENT_DIR}

if [[ -n ${TRAVIS_TAG+x} ]]; then
    ARCHIVE_NAME="bestitamazonpay4oxid-${TRAVIS_TAG}"
elif [[ -n ${TRAVIS_BRANCH+x} ]]; then
    ARCHIVE_NAME="bestitamazonpay4oxid-${TRAVIS_BRANCH}"
fi

# Install needed packages
composer install --no-dev --ignore-platform-reqs

# Create temp dir and copy source
mkdir -p "${TEMP_DIR}${BASE_DIR}"
cp -R ./* "${TEMP_DIR}${BASE_DIR}"

cat <<EOF > ${TEMP_DIR}/${VENDOR_DIR}/vendormetadata.php
<?php
\$sVendorMetadataVersion = '1.0';
EOF

# Switch to temp dir
cd ${TEMP_DIR}

# Create source archive
echo "=== Create zip archives ==="
zip -r \
    --exclude=*bestitamazonpay4oxidparameterhandler_encoded.php \
    --exclude=*.git* \
    --exclude=*build.sh \
    --exclude=*deploy.sh \
    --exclude=*.travis.yml \
    "${CURRENT_DIR}/${ARCHIVE_NAME}-source.zip" ./*

zip -r \
    --exclude=*.git* \
    --exclude=*build.sh \
    --exclude=*deploy.sh \
    --exclude=*.travis.yml \
    --exclude=*legacy* \
    "${CURRENT_DIR}/${ARCHIVE_NAME}.zip" ./*

zip -r \
    --exclude=*.git* \
    --exclude=*build.sh \
    --exclude=*deploy.sh \
    --exclude=*.travis.yml \
    --exclude=*legacy* \
    --exclude=*vendor* \
    --exclude=*tests/additional.inc.php \
    "${CURRENT_DIR}/${ARCHIVE_NAME}-oxid-6.zip" ./*

# Create legacy archive
zip -r \
    --exclude=*.git* \
    --exclude=*build.sh \
    --exclude=*deploy.sh \
    --exclude=*.travis.yml \
    "${CURRENT_DIR}/${ARCHIVE_NAME}-legacy.zip" ./*

# Switch back to start current dir and remove the temp folder
cd ${CURRENT_DIR}
rm -r "${TEMP_DIR}"

# Deploy to test server
rrun() {
    echo "${DEPLOYMENT_USER}@${DEPLOYMENT_SERVER}: ${1}"
    ssh -o strictHostKeyChecking=no ${DEPLOYMENT_USER}@${DEPLOYMENT_SERVER} "${1}"
}

deployAndActive() {
    echo "=== Deploy and activate ==="
    local ARCHIVE_NAME=${1}
    local DEPLOYMENT_HTDOCS_DIR=${2}

    # Copy archive
    echo "Place module"
    scp ${CURRENT_DIR}/${ARCHIVE_NAME} ${DEPLOYMENT_USER}@${DEPLOYMENT_SERVER}:/tmp
    rrun "if [[ -d /tmp/copy_this ]]; then rm -R /tmp/copy_this; fi"
    rrun "unzip -o /tmp/${ARCHIVE_NAME} -d /tmp && rm /tmp/${ARCHIVE_NAME}"
    rrun "if [[ -d ${DEPLOYMENT_HTDOCS_DIR}/modules/bestit ]]; then rm -R ${DEPLOYMENT_HTDOCS_DIR}/modules/bestit; fi"
    rrun "mv /tmp/copy_this/modules/* ${DEPLOYMENT_HTDOCS_DIR}/modules"
    rrun "rm -R /tmp/copy_this"

    local CONSOLE_BIN="${DEPLOYMENT_HTDOCS_DIR}/console/bin/oxid"
    rrun "chmod +x ${CONSOLE_BIN}"

    # Activate module
    echo "Activate module"
    rrun "${CONSOLE_BIN} fix:states bestitamazonpay4oxid"
    rrun "${CONSOLE_BIN} db:update"
    rrun "${CONSOLE_BIN} cache:clear"
}

deployAndActive "${ARCHIVE_NAME}.zip" "${OXID5_DEPLOYMENT_HTDOCS_DIR}"
deployAndActive "${ARCHIVE_NAME}-oxid-6.zip" "${OXID6_DEPLOYMENT_HTDOCS_DIR}"