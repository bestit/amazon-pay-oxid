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
$ docker-compose exec web /bin/bash -c "chmod +x ./build/files/build.sh && ./build/files/build.sh"
```
This command will build an local oxid 6 instance with installed demodata and the symlinked module. 
You are now ready to develop or tests your changes. The shop will be accessible via the url http://localhost:8100 and
the admin will be reachable via http://localhost:8100 and the credentials admin:admin.

#### Modify the build
You can change the oxid series and version that will be build by the OXID_SERIES and OXID_VERSION env variables that are defined in the docker-compose.yml file.
Additionally you can change the exposed apache port that is used for the shop. This change will also be done in the docker-compose.yml file.

#### Running the tests
At the moment the test script requires and previously build instance of the oxid shop. 
```bash
$ docker-compose exec web /bin/bash -c "chmod +x ./build/files/test.sh"
```