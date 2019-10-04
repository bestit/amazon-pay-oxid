#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${1+x}" ]]; then
    echo "Shop version parameter needed"
    exit
fi

OXID_VERSION=${1}
RUN_TESTS=0

if [[ -n "${2+x}" && ${2} == "dev" ]]; then
    SHOP_DIR=${APP_DIR}
else
    SHOP_DIR="/tmp/build_${OXID_VERSION}"
    RUN_TESTS=1
fi

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

# Create shop dir
if [[ -d ${SHOP_DIR} ]]; then
    rm -R -f ${SHOP_DIR}
fi

SHOP_WEB_DIR="${SHOP_DIR}/source"
VENDOR_DIR="${SHOP_WEB_DIR}/modules/bestit"
MODULE_TARGET_DIR="${VENDOR_DIR}/amazonpay4oxid"

# Setup shop
echo "=== Setup shop ==="
if [[ ${OXID_VERSION} =~ ^5\.([0-9]+).* ]]; then
    git clone --branch b-${OXID_VERSION}-ce --depth 1 https://github.com/OXID-eSales/oxideshop_ce.git ${SHOP_DIR}
    composer install -n -d ${SHOP_WEB_DIR} --ignore-platform-reqs
    wget "https://raw.githubusercontent.com/OXID-eSales/oxideshop_demodata_ce/b-${OXID_VERSION}/src/demodata.sql" -P ${SHOP_WEB_DIR}/setup/sql/

    sed -i 's|<dbHost_ce>|'${DB_HOST}'|; s|<dbName_ce>|oxidehop_ce|; s|<dbUser_ce>|'${DB_USER}'|; s|<dbPwd_ce>|'${DB_PASS}'|; s|<sShopURL_ce>|http://localhost:'${HTTP_PORT}/'|; s|<sSSLShopURL_ce>|https://localhost:'${HTTPS_PORT}/'|; s|<sShopDir_ce>|'${SHOP_WEB_DIR}'|; s|<sCompileDir_ce>|'${SHOP_WEB_DIR}'/tmp|; s|<iUtfMode>|0|; s|$this->iDebug = 0|$this->iDebug = 1|; s|mysql|mysqli|' ${SHOP_WEB_DIR}/config.inc.php

    # Setup flow theme
    echo "=== Setup flow theme ==="
    cd ${SHOP_WEB_DIR}/application/views
    git clone https://github.com/OXID-eSales/flow_theme.git flow --branch b-1.0
    cp -R flow/out/flow ../../out/

    DB_SCHEMA_FILE=${SHOP_WEB_DIR}/setup/sql/database_schema.sql
    DB_DEMO_DATA_FILE=${SHOP_WEB_DIR}/setup/sql/demodata.sql
    TEST_CONFIG_DIR=${SHOP_WEB_DIR}
else
    mkdir -p ${SHOP_DIR}
    composer create-project -n --no-progress --working-dir=${SHOP_DIR} oxid-esales/oxideshop-project ${SHOP_DIR} ${OXID_VERSION}
    cp ${SHOP_WEB_DIR}/config.inc.php.dist ${SHOP_WEB_DIR}/config.inc.php

    sed -i 's|<dbHost>|'${DB_HOST}'|; s|<dbName>|oxidehop_ce|; s|<dbUser>|'${DB_USER}'|; s|<dbPwd>|'${DB_PASS}'|; s|<sShopURL>|https://localhost:'${HTTPS_PORT}/'|; s|<sSSLShopURL>|https://localhost:'${HTTPS_PORT}/'|; s|<sShopDir>|'${SHOP_WEB_DIR}'|; s|<sCompileDir>|'${SHOP_WEB_DIR}'/tmp|; s|$this->iDebug = 0|$this->iDebug = 0|' ${SHOP_WEB_DIR}/config.inc.php
    sed -i "s|\$this->edition = ''|\$this->edition = 'CE'|" ${SHOP_WEB_DIR}/config.inc.php

    DB_SCHEMA_FILE=${SHOP_WEB_DIR}/Setup/Sql/database_schema.sql
    DB_DEMO_DATA_FILE=${SHOP_DIR}/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql
    TEST_CONFIG_DIR=${SHOP_DIR}
fi

echo "=== Prepare DB ==="
mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < ${DB_SCHEMA_FILE}
mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < ${DB_DEMO_DATA_FILE}

if [[ -f ${SHOP_DIR}/vendor/bin/oe-eshop-db_views_regenerate ]]; then
    echo "=== Regenerate views ==="
    ${SHOP_DIR}/vendor/bin/oe-eshop-db_views_regenerate
fi

echo "=== Prepare test config ==="
sed -i "s|partial_module_paths: null|partial_module_paths: bestit/amazonpay4oxid|; s|run_tests_for_shop: true|run_tests_for_shop: false|" ${TEST_CONFIG_DIR}/test_config.yml

echo "=== Link amazon module ==="
mkdir -p ${VENDOR_DIR}
cat <<EOF > ${VENDOR_DIR}/vendormetadata.php
<?php
\$sVendorMetadataVersion = '1.0';
EOF
rm -rf ${MODULE_TARGET_DIR}
ln -s ${MODULE_DIR} ${MODULE_TARGET_DIR}
composer install -d ${MODULE_TARGET_DIR} --ignore-platform-reqs

if [[ -f ${SHOP_DIR}/vendor/bin/oe-console ]]; then
    ${SHOP_DIR}/vendor/bin/oe-console oe:module:install-configuration ${MODULE_TARGET_DIR}
fi

echo "=== Finalize Permissions ==="
chown -R www-data:www-data ${SHOP_WEB_DIR}
sudo chmod 777 -R ${SHOP_WEB_DIR}/config.inc.php \
    ${SHOP_WEB_DIR}/.htaccess \
    ${SHOP_WEB_DIR}/tmp/ \
    ${SHOP_WEB_DIR}/log/ \
    ${SHOP_WEB_DIR}/out/pictures/ \
    ${SHOP_WEB_DIR}/out/media/ \
    ${SHOP_WEB_DIR}/export

if [[ -f ${SHOP_DIR}/var/configuration ]]; then
    sudo chmod 777 -R ${SHOP_DIR}/var/configuration
fi

echo "=== Build finished ==="

if [[ ${RUN_TESTS} == 1 ]]; then
    if [[ -f "${SHOP_DIR}/source/vendor/bin/runtests" ]]; then
        ${SHOP_DIR}/source/vendor/bin/runtests
    else
        unset TRAVIS_ERROR_LEVEL
        echo ${SHOP_DIR}/vendor/bin/runtests
        ${SHOP_DIR}/vendor/bin/runtests
    fi
fi