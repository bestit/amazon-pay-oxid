#!/usr/bin/env bash
set -euo pipefail

if [ ! -n ${1:-} ]; then
    OXID_SERIES=${1}
fi

if [ ! -n ${2:-} ]; then
    OXID_VERSION=${2}
fi

echo -ne "Waiting for mysql service"
while ! mysqladmin ping -h${DB_HOST} -P 3306 --silent; do
    echo -ne "."
    sleep 1
done

echo "=== Generate default database ==="
mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} -Doxidehop_ce << QUERY_INPUT
SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';
QUERY_INPUT

echo "=== Start build for OXID ${OXID_VERSION} ==="
BASE_DIR="/var/www"
APP_DIR="/var/www/amazonpay4oxid"
SHOP_DIR="${BASE_DIR}/html"
SHOP_WEB_DIR="${SHOP_DIR}/source"
VENDOR_DIR="${SHOP_WEB_DIR}/modules/bestit"
MODULE_BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"

# Create shop dir
if [[ -d ${SHOP_DIR} ]]; then
    rm -R -f ${SHOP_DIR}
fi

cd ${BASE_DIR}

# Setup shop
echo "=== Setup shop ==="
if [[ ${OXID_SERIES} == 5 ]]; then
    git clone --branch ${OXID_VERSION} --depth 1 https://github.com/OXID-eSales/oxideshop_ce.git html
    composer install -n -d ${SHOP_WEB_DIR} --ignore-platform-reqs
    wget "https://raw.githubusercontent.com/OXID-eSales/oxideshop_demodata_ce/b-5.3/src/demodata.sql" -P ${SHOP_WEB_DIR}/setup/sql/

    DB_SCHEMA_FILE=${SHOP_WEB_DIR}/setup/sql/database_schema.sql
    DB_DEMO_DATA_FILE=${SHOP_WEB_DIR}/setup/sql/demodata.sql
    TEST_CONFIG_DIR=${SHOP_WEB_DIR}
else
    git clone --branch ${OXID_VERSION} --depth 1 https://github.com/OXID-eSales/oxideshop_ce.git html

    CUSTOM_LOCK_FILE=${APP_DIR}/build/shops/composer_${OXID_VERSION}.lock
    if [[ -f "$CUSTOM_LOCK_FILE" ]]; then
        echo "=== Copy custom composer lock file ==="
        cp ${CUSTOM_LOCK_FILE} ${SHOP_DIR}/composer.lock
    fi

    SHOP_PATH='source'
    SHOP_TESTS_PATH='tests'
    MODULES_PATH=''
    composer install -n -d ${SHOP_DIR}
    cp ${SHOP_WEB_DIR}/config.inc.php.dist ${SHOP_WEB_DIR}/config.inc.php

    DB_SCHEMA_FILE=${SHOP_WEB_DIR}/Setup/Sql/database_schema.sql
    DB_DEMO_DATA_FILE=${SHOP_DIR}/vendor/oxid-esales/oxideshop-demodata-ce/src/demodata.sql
    TEST_CONFIG_DIR=${SHOP_DIR}
fi
echo "=== Rewrite config ==="
if [[ ${OXID_SERIES} == 5 ]]; then
    sed -i 's|<dbHost_ce>|'${DB_HOST}'|; s|<dbName_ce>|oxidehop_ce|; s|<dbUser_ce>|'${DB_USER}'|; s|<dbPwd_ce>|'${DB_PASS}'|; s|<sShopURL_ce>|http://localhost:'${APACHE_PORT}/'|; s|<sShopDir_ce>|'${SHOP_WEB_DIR}'|; s|<sCompileDir_ce>|'${SHOP_WEB_DIR}'/tmp|; s|<iUtfMode>|0|; s|$this->iDebug = 0|$this->iDebug = 1|; s|mysql|mysqli|' ${SHOP_WEB_DIR}/config.inc.php
else
    sed -i 's|<dbHost>|'${DB_HOST}'|; s|<dbName>|oxidehop_ce|; s|<dbUser>|'${DB_USER}'|; s|<dbPwd>|'${DB_PASS}'|; s|<sShopURL>|http://localhost:'${APACHE_PORT}/'|; s|<sShopDir>|'${SHOP_WEB_DIR}'|; s|<sCompileDir>|'${SHOP_WEB_DIR}'/tmp|; s|$this->iDebug = 0|$this->iDebug = 0|' ${SHOP_WEB_DIR}/config.inc.php
fi
sed -i "s|\$this->edition = ''|\$this->edition = 'CE'|" ${SHOP_WEB_DIR}/config.inc.php

echo "=== Prepare DB ==="
mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < ${DB_SCHEMA_FILE}
mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < ${DB_DEMO_DATA_FILE}

if [[ ${OXID_SERIES} == 5 ]]; then
    # Setup flow theme
    echo "=== Setup flow theme ==="
    cd ${SHOP_WEB_DIR}/application/views
    git clone https://github.com/OXID-eSales/flow_theme.git flow --branch b-1.0
    cp -R flow/out/flow ../../out/
else
    echo "=== Finalize DB ==="
    ${SHOP_DIR}/vendor/bin/oe-eshop-db_views_regenerate
fi

echo "=== Prepare test config ==="
rm ${TEST_CONFIG_DIR}/test_config.yml
cp ${APP_DIR}/test_config.yml ${TEST_CONFIG_DIR}
sed -i "s|shop_path: ''|shop_path: source|; s|shop_tests_path: ../tests|shop_tests_path: tests|" ${TEST_CONFIG_DIR}/test_config.yml

echo "=== Link amazon module ==="
mkdir -p ${VENDOR_DIR}
cat <<EOF > ${VENDOR_DIR}/vendormetadata.php
<?php
\$sVendorMetadataVersion = '1.0';
EOF
rm -rf ${MODULE_BASE_DIR}
ln -s ${APP_DIR} ${VENDOR_DIR}
composer install -d ${MODULE_BASE_DIR} --ignore-platform-reqs

echo "=== Finalize Permissions ==="
chown -R www-data:www-data ${SHOP_WEB_DIR}
sudo chmod 777 -R ${SHOP_WEB_DIR}/config.inc.php ${SHOP_WEB_DIR}/.htaccess ${SHOP_WEB_DIR}/tmp/ ${SHOP_WEB_DIR}/log/ ${SHOP_WEB_DIR}/out/pictures/ ${SHOP_WEB_DIR}/out/media/ ${SHOP_WEB_DIR}/export
echo "=== Build finished ==="