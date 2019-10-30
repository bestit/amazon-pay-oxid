#!/usr/bin/env bash
set -euo pipefail

GH_API_TOKEN=${1}
TAG=${2}
GH_OWNER='bestit'
GH_REPO_NAME='amazon-pay-oxid'
GH_API="https://api.github.com"
GH_REPO="$GH_API/repos/${GH_OWNER}/${GH_REPO_NAME}"
GH_TAGS="$GH_REPO/releases/tags/${TAG}"
AUTH="Authorization: token ${GH_API_TOKEN}"
WGET_ARGS="--content-disposition --auth-no-challenge --no-cookie"
CURL_ARGS="-LJO#"

if [[ "${TAG}" == 'LATEST' ]]; then
  GH_TAGS="${GH_REPO}/releases/latest"
fi

# Validate token.
curl -o /dev/null -sH "$AUTH" ${GH_REPO} || {
    echo "Error: Invalid repo, token or network issue!"
    exit 1
}

# Read asset tags.
RESPONSE=$(curl -sH "$AUTH" ${GH_TAGS})

# Get ID of the asset based on given filename.
GH_ID=$(echo "${RESPONSE}" | grep -m 1 "id.:" | sed -n 's/.*"id": \([0-9]\+\).*/\1/p' )

if [[ -z ${GH_ID+x} ]]; then
    echo "Error: Failed to get release id for tag: ${TAG}"
    echo "${RESPONSE}" | awk 'length($0)<100' >&2
    exit 1
fi

# Build package
CURRENT_DIR=${PWD}
TEMP_DIR='/tmp/module_build'
VENDOR_DIR='/copy_this/modules/bestit'
BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"
ARCHIVE_NAME="bestitamazonpay4oxid"
MODULE_VERSION=$(cat ./metadata.php | sed -n "s/.*'version' => '\([0-9]\+\.[0-9]\+\.[0-9]\+\)',.*/\1/p")
ARCHIVE_NAME="bestitamazonpay4oxid-oxid5-${MODULE_VERSION}"

if [[ -d ${CURRENT_DIR}/vendor ]]; then
    sudo rm -rf ${CURRENT_DIR}/vendor
fi

sudo chmod -R 777 ${CURRENT_DIR}

# Install needed dependencies
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
PACKAGE="${CURRENT_DIR}/${ARCHIVE_NAME}.zip"

zip -r \
    --exclude=*.git* \
    --exclude=*build.sh \
    --exclude=*deploy-package.sh \
    --exclude=*deploy.sh \
    --exclude=*.travis.yml \
    ${PACKAGE} ./*

# Switch back to start current dir and remove the temp folder
cd ${CURRENT_DIR}
rm -r "${TEMP_DIR}"

# Upload package
echo "Uploading package... "

# Construct url
GH_ASSET="https://uploads.github.com/repos/${GH_OWNER}/${GH_REPO_NAME}/releases/${GH_ID}/assets?name=$(basename ${PACKAGE})"
curl --data-binary @"${PACKAGE}" -H "Authorization: token ${GH_API_TOKEN}" -H "Content-Type: application/octet-stream" ${GH_ASSET} > /dev/null

echo "Done!"
