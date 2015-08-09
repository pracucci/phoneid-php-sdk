# Phone.id PHP SDK

This PHP SDK allows you to easily implement the Phone.id authentication on web. To see how it works, please take a look at `examples/authentication`.


### What's Phone.id?

Phone.id is a service that makes "login with your mobile number" so easy that hurts.


### How to register an app

To register an app and get Phone.id credentials, you should:

1. Login to https://developer.phone.id with your mobile phone
2. Create a new app and get client id and secret


### Contributing

Run tests before pushing a pull request:

``` bash
phpunit --bootstrap src/PhoneId/autoload.php tests
```
