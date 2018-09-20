# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

## Official Documentation

Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).

## Api Documentation

#### Place order

- Method: `POST`
- URL path: `/order`
- Request body:

  ```
  {
      "origin": ["START_LATITUDE", "START_LONGTITUDE"],
      "destination": ["END_LATITUDE", "END_LONGTITUDE"]
  }
  ```

- Response:

  Header: `HTTP 200`
  Body:

  ```
  {
      "id": <order_id>,
      "distance": <total_distance>,
      "status": "UNASSIGN"
  }
  ```

  or

  Header: `HTTP 500`
  Body:

  ```json
  {
    "error": "ERROR_DESCRIPTION"
  }
  ```

#### Take order

- Method: `PUT`
- URL path: `/order/:id`
- Request body:
  ```
  {
      "status":"taken"
  }
  ```
- Response:
  Header: `HTTP 200`
  Body:

  ```
  {
      "status": "SUCCESS"
  }
  ```

  or

  Header: `HTTP 409`
  Body:

  ```
  {
      "error": "ORDER_ALREADY_BEEN_TAKEN"
  }
  ```

#### Order list

- Method: `GET`
- Url path: `/orders?page=:page&limit=:limit`
- Response:

  ```
  [
      {
          "id": <order_id>,
          "distance": <total_distance>,
          "status": <ORDER_STATUS>
      },
      ...
  ]
  ```
