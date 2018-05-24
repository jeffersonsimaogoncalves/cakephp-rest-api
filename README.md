# RestApi plugin for CakePHP 3

This plugin provides basic support for building REST API services in your CakePHP 3 application. Read a detailed guide on how to implement this here - [CakePHP: Build REST APIs with RestApi plugin](http://blog.narendravaghela.com/cakephp-build-rest-apis-with-restapi-plugin-part-1/)

## Requirements
This plugin has the following requirements:

* CakePHP 3.6.0 or greater.
* PHP 7.0 or greater.

## Installation
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:
```
composer require multidots/cakephp-rest-api
```
After installation, [Load the plugin](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin)
```php
Plugin::load('RestApi', ['bootstrap' => true]);
```
Or, you can load the plugin using the shell command
```sh
$ bin/cake plugin load -b RestApi
```
## Usage
You just need to create your API related controller and extend it to `ApiController` instead of default `AppController`.  You just need to set you results in `apiResponse` variable and your response code in `httpStatusCode` variable. For example,
```php
namespace App\Controller;

use RestApi\Controller\ApiController;

/**
 * Foo Controller
 */
class FooController extends ApiController
{

    /**
     * bar method
     *
     */
    public function bar()
    {
		// your action logic

		// Set the HTTP status code. By default, it is set to 200
		$this->httpStatusCode = 200;

		// Set the response
		$this->apiResponse['you_response'] = 'your response data';
    }
}
```
You can define your logic in your action function as per your need. For above example, you will get following response in `json` format,
```json
{"status":"OK","result":{"you_response":"your response data"}}
```
The URL for above example will be `http://yourdomain.com/foo/bar`. You can customize it by setting the routes in `APP/config/routes.php`.

Simple :)

## Configurations
This plugin provides several configuration related to Response Format, `CORS` , Request Logging and `JWT` authentication. The default configurations are as below and defined in `RestApi/config/api.php`.
```php
<?php

return [
    'ApiRequest' => [
        'debug' => false,
    	'responseFormat' => [
            'statusKey' => 'status',
            'statusOkText' => 'OK',
            'statusNokText' => 'NOK',
            'resultKey' => 'result',
            'messageKey' => 'message',
            'defaultMessageText' => 'Empty response!',
            'errorKey' => 'error',
            'defaultErrorText' => 'Unknown request!'
        ],
        'log' => false,
        'jwtAuth' => [
            'enabled' => true,
            'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
            'tokenAlgorithm' => 'HS256'
        ],
        'cors' => [
            'enabled' => true,
            'origin' => '*',
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```
> Set `debug` to true in your development environment to get original exception messages in response.

### Request authentication using JWT
You can check for presence of auth token in API request. By default it is enabled. You need to define a flag `allowWithoutToken` to `true` or `false`. For example,
```php
$routes->connect('/demo/foo', ['controller' => 'Demo', 'action' => 'foo', 'allowWithoutToken' => false]);
```
Above API method will require auth token in request. You can pass the auth token in either header, in GET parameter or in POST field.

If you want to pass token in header, use below format.
```php
Authorization: Bearer [token]
```
In case of GET or POST parameter, pass the token in `token` parameter.

#### Generate jwt token
This plugin provides Utility class to generate jwt token and sign with same key and algorithm. Use `JwtToken::generate()` method wherever required. Most probably, you will need this in user login and register API. See below example,
```php
<?php

namespace App\Controller;

use RestApi\Controller\ApiController;
use RestApi\Utility\JwtToken;

/**
 * Account Controller
 *
 */
class AccountController extends ApiController
{

    /**
     * Login method
     *
     * Returns a token on successful authentication
     *
     * @return void|\Cake\Network\Response
     */
    public function login()
    {
        $this->request->allowMethod('post');

        /**
         * process your data and validate it against database table
         */
        
		// generate token if valid user
		$payload = ['email' => $user->email, 'name' => $user->name];

        $this->apiResponse['token'] = JwtToken::generateToken($payload);
        $this->apiResponse['message'] = 'Logged in successfully.'; 
    }
}
```

### cors
By default, cors requests are enabled and allowed from all domains. You can overwrite these settings by creating config file at `APP/config/api.php`. The content of file will look like,
```php
<?php
return [
    'ApiRequest' => [
        'cors' => [
            'enabled' => true,
            'origin' => '*',
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```
To disable cors request, set `enabled` flag to `false`. To allow requests from specific domains, set them in `origin` option like,
```php
<?php
return [
    'ApiRequest' => [
        'cors' => [
            'enabled' => true,
            'origin' => ['localhost', 'www.example.com', '*.example.com'],
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```
### Log request & response
By default, request log is disabled. You can overwrite this by creating/updating config file at `APP/config/api.php` . The content of file will look like,
```php
<?php
return [
    'ApiRequest' => [
        'log' => true,
        // other config options
    ]
];
```
After enabling the log, you need to create a table in your database. Below is the table structure.
```sql
CREATE TABLE IF NOT EXISTS `api_requests` (
  `id` char(36) NOT NULL,
  `http_method` varchar(10) NOT NULL,
  `endpoint` varchar(2048) NOT NULL,
  `token` varchar(2048) DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `request_data` longtext,
  `response_code` int(5) NOT NULL,
  `response_data` longtext,
  `exception` longtext,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
Or you can use the `bake` command to automatically generate the above table.
```sh
$ bin/cake migrations migrate --plugin RestApi
```
## Response format
The default response format of API is `json` and its structure is defined as below.
```json
{
  "status": "OK",
  "result": {
    //your result data
  }
}
```
If you have set httpResponseCode to any value other that 200, the `status` value will be `NOK` otherwise `OK`. In case of exceptions, it will be handled automatically and set the appropriate status code.
> You can modify the default response configuration like the text for OK response, key for main response data, etc. by overwriting them  in your `APP/config/api.php` file.

## Examples
Below are few examples to understand how this plugin works.

### Retrieve articles
Let's create an API which returns a list of articles with basic details like id and title. Our controller will look like,
```php
<?php

namespace App\Controller;

use RestApi\Controller\ApiController;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends ApiController
{

    /**
     * index method
     *
     */
    public function index()
    {
        $articles = $this->Articles->find('all')
            ->select(['id', 'title'])
            ->toArray();

        $this->apiResponse['articles'] = $articles;
    }
}
```
The response of above API call will look like,
```json
{
  "status": "OK",
  "result": {
    "articles": [
      {
        "id": 1,
        "title": "Lorem ipsum"
      },
      {
        "id": 2,
        "title": "Donec hendrerit"
      }
    ]
  }
}
```
### Exception handling
This plugin will handle the exceptions being thrown from your action. For example, if you API method only allows `POST` method and someone makes a `GET` request, it will generate `NOK` response with proper HTTP response code. For example, 
```php
<?php

namespace App\Controller;

use RestApi\Controller\ApiController;

/**
 * Foo Controller
 *
 */
class FooController extends ApiController
{

    /**
     * bar method
     *
     */
    public function restricted()
    {
        $this->request->allowMethod('post');
        // your other logic will be here
        // and finally set your response
        // $this->apiResponse['you_response'] = 'your response data';
    }
}
```
The response will look like,
```json
{"status":"NOK","result":{"message":"Method Not Allowed"}}
```
Another example of throwing an exception,
```php
<?php

namespace App\Controller;

use Cake\Network\Exception\NotFoundException;
use RestApi\Controller\ApiController;

/**
 * Foo Controller
 *
 */
class FooController extends ApiController
{

    /**
     * error method
     *
     */
    public function error()
    {
        $throwException = true;

        if ($throwException) {
            throw new NotFoundException();
        }

        // your other logic will be here
        // and finally set your response
        // $this->apiResponse['you_response'] = 'your response data';
    }
}
```
And the response will be,
```json
{"status":"NOK","result":{"message":"Not Found"}}
```
## Reporting Issues
If you have a problem with this plugin or any bug, please open an issue on [GitHub](https://github.com/multidots/cakephp-rest-api/issues).

## Credits

This work is based on the [code by Multidots](https://github.com/multidots/cakephp-rest-api).
