# Amazon Pay & Login for OXID eShop

[![Build Status](https://travis-ci.org/bestit/amazon-pay-oxid.svg?branch=develop)](https://travis-ci.org/bestit/amazon-pay-oxid)

Pay with Amazon is fast, easy and secure and can help you add new customers, increase sales, and reduce fraud costs. It provides a seamless experience for your customers. All buyer interactions take place in widgets, so they never leave your site. Customers simply log in using their Amazon credentials, select a shipping address and payment method, and confirm their order

The Login and Pay with Amazon for Oxideshop extension adds two components to your OxideShop installation:

* A ‘Login with Amazon’ module that allows customers to seamlessly register and log in using their Amazon account credentials.
* A payments module ‘Pay with Amazon’ that allows customers to complete the checkout using the payment methods stored in their Amazon account.
 
Enable Login and Pay with Amazon on your OXID eShop, you will benefit from:

* Online identity

Your customers save time and hassle by using their Amazon credentials to login and pay; so you increase repeat buyers by offering a trusted and convenient payment method.

* A smooth payment process

Your customers won’t leave your website to login or enter their payment/shipping information; so you capture sales from buyers who are reluctant to enter their information.

You will need to create an Amazon Payments Seller Account to use the Pay with Amazon for OXID eShop extension. This account is where you will receive payments, update your account settings and view settlement reports.

## Development Setup

In order to run a oxid 6 instance locally to develop or test features, you have to run the following steps:

#### Starting the docker stack
```bash
$ docker-compose up -d
```
The first time will build the images and this could take some time! 

#### Build the instance
```bash
$ docker-compose exec web /bin/bash -c "chmod +x ./scripts/build.sh && ./scripts/build.sh <version> dev"
```
This command will build an local oxid with the given version (use for example `5.3`, for version lower than 6.0 or `dev-b-6.0-ce`, for version from 6.0 on) instance with installed demodata and the symlinked module. 
You are now ready to develop or tests your changes. The shop will be accessible via the url http://localhost:8100 and
the admin will be reachable via http://localhost:8100 or with https under https://localhost:4444 and the credentials admin:admin.

#### Running the tests
Just run the following command:
```bash
$ docker-compose exec web /bin/bash -c "chmod +x ./scripts/build.sh && ./scripts/build.sh <version>"
```
This will create a fresh instance and runs the tests. May also want to login to your previous build test instance and run the test there just access the docker container by running the following command.
```bash
$ docker-compose exec web bash
```
Now you are logged in and are able to run the test from the instance vendor dir `vendor/bin/runtests`.