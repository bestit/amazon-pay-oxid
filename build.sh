#!/usr/bin/env bash
set -euo pipefail

OXID_VERSION=${1}
DB_HOST=oxiddb
DB_USER=root
DB_PASS=dbpass

echo -ne "Waiting for mysql service"
while ! mysqladmin ping -h${DB_HOST} -P 3306 --silent; do
    echo -ne "."
    sleep 1
done
echo ""

echo "=== Generate default database ==="
mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} -Doxidehop_ce << QUERY_INPUT
SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';
QUERY_INPUT

echo "=== Start build for OXID ${OXID_VERSION} ==="

CURRENT_DIR=${PWD}
TEMP_DIR="/tmp/build_${OXID_VERSION}"
BASE_DIR="${TEMP_DIR}/oxideshop_ce"
SHOP_DIR="${BASE_DIR}/source"
VENDOR_DIR="${SHOP_DIR}/modules/bestit"
MODULE_BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"

# Create tmp dir
if [[ -d ${TEMP_DIR} ]]; then
    rm -R -f ${TEMP_DIR}
fi

mkdir -p ${TEMP_DIR}
cd ${TEMP_DIR}

# Setup shop
echo "=== Setup shop ==="
if [[ ${OXID_VERSION} == 5 ]]; then
    git clone --branch b-5.3-ce --depth 1 https://github.com/OXID-eSales/oxideshop_ce.git
    composer install -n -d ${SHOP_DIR} --ignore-platform-reqs
    sed -i 's|<dbHost_ce>|'${DB_HOST}'|; s|<dbName_ce>|oxidehop_ce|; s|<dbUser_ce>|'${DB_USER}'|; s|<dbPwd_ce>|'${DB_PASS}'|; s|<sShopURL_ce>|http://127.0.0.1|; s|<sShopDir_ce>|'${SHOP_DIR}'|; s|<sCompileDir_ce>|'${SHOP_DIR}'/tmp|; s|<iUtfMode>|0|; s|$this->iDebug = 0|$this->iDebug = 1|; s|mysql|mysqli|' ${SHOP_DIR}/config.inc.php
    wget "https://raw.githubusercontent.com/OXID-eSales/oxideshop_demodata_ce/b-5.3/src/demodata.sql" -P oxideshop_ce/source/setup/sql/

    # Setup flow theme
    echo "=== Setup flow theme ==="
    cd oxideshop_ce/source/application/views
    git clone https://github.com/OXID-eSales/flow_theme.git flow --branch b-1.0
    cp -R flow/out/flow ../../out/

    cp ${CURRENT_DIR}/test_config.yml ${SHOP_DIR}
else
    git clone --branch b-${OXID_VERSION}.x --depth 1 https://github.com/OXID-eSales/oxideshop_ce.git

    SHOP_PATH='source'
    SHOP_TESTS_PATH='tests'
    MODULES_PATH=''
    composer install -n -d ${BASE_DIR}
    cp ${SHOP_DIR}/config.inc.php.dist ${SHOP_DIR}/config.inc.php
    sed -i 's|<dbHost>|'${DB_HOST}'|; s|<dbName>|oxidehop_ce|; s|<dbUser>|'${DB_USER}'|; s|<dbPwd>|'${DB_PASS}'|; s|<sShopURL>|http://localhost|; s|<sShopDir>|'${SHOP_DIR}'|; s|<sCompileDir>|'${SHOP_DIR}'/tmp|; s|$this->iDebug = 0|$this->iDebug = 1|' ${SHOP_DIR}/config.inc.php
    sed -i "s|\$this->edition = ''|\$this->edition = 'CE'|" ${SHOP_DIR}/config.inc.php

    rm ${BASE_DIR}/test_config.yml
    cp ${CURRENT_DIR}/test_config.yml ${BASE_DIR}
    sed -i "s|shop_path: ''|shop_path: source|; s|shop_tests_path: ../tests|shop_tests_path: tests|" ${BASE_DIR}/test_config.yml
fi

# Copy amazon module
echo "=== Copy amazon module ==="
mkdir -p ${MODULE_BASE_DIR}
cp -R ${CURRENT_DIR}/* ${MODULE_BASE_DIR}
cat <<EOF > ${VENDOR_DIR}/vendormetadata.php
<?php
\$sVendorMetadataVersion = '1.0';
EOF
composer install -d ${MODULE_BASE_DIR} --ignore-platform-reqs

# Setup and run unit tests
echo "=== Setup unit tests and run them ==="
TEST_SUITE="${MODULE_BASE_DIR}/tests/"
cd ${MODULE_BASE_DIR}/tests/

if [[ ${OXID_VERSION} == 5 ]]; then
    ${SHOP_DIR}/vendor/bin/runtests
else
    apt-get -y install sudo # oxid needs sudo -.-
    unset TRAVIS_ERROR_LEVEL
    ${BASE_DIR}/vendor/bin/runtests
fi
