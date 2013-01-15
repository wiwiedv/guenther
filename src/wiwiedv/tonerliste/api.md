# Tonerliste

## Conventions

### Enums

* `items.type` is one numerical value out of
  * `1` meaning toner
  * `2` meaning drum
* `items.color` is one numerical value out of
  * `1` meaning black
  * `2` meaning magenta
  * `3` meaning cyan
  * `4` meaning yellow
  * `5` meaning universal
* `items.hidden` is one numerical value out of
  * `0` meaning visible
  * `1` meaning hidden
* `transactions.action` is one numerical value out of
  * `1` meaning deposit
  * `2` meaning withdraw
  * `3` meaning create
  * `4` meaning modify

## Database Structure

### Table `items`
    CREATE TABLE `items` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
      `name` TEXT NOT NULL UNIQUE,
      `type` INTEGER NOT NULL,
      `color` INTEGER NOT NULL,
      `printer` TEXT NOT NULL,
      `stock` INTEGER NOT NULL DEFAULT 0,
      `hidden` INTEGER NOT NULL DEFAULT 0
    );

### Table `transactions`
    CREATE TABLE `transactions` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
      `date` INTEGER NOT NULL,
      `action` INTEGER NOT NULL,
      `user` TEXT NOT NULL,
      `item` INTEGER NOT NULL,
      `reason` TEXT NOT NULL
    );

## Exposed Data Structure

### The `toner`

The `toner` is an `item`.

### The `drum`

The `drum` is an `item`.

### The `item`

The `item` represents either a `toner` or a `drum`. The data is structured as follows:

* `id` _integer_, unique
* `name` _string_,
* `type` _integer_, see Enums
* `color` _integer_, see Enums
* `printer` _string_
* `stock` _integer_
* `hidden` _boolean_, see Enums

### The `transaction`

The `transaction` represents a modification of an `item`. The data is structured as follows:

* `id` _integer_, unique
* `date` _integer_
* `action` _integer_, see Enums
* `user` _string_
* `item` _integer_, refers to `item.id`
* `reason` _string_

## Exposed API

### Commonalities

##### Request Header

* `Authorization` Provide HTTP Basic authorization credentials

##### Response Header

* `X-Guenther-Version` Guenther version number

### Resources

#### Requesting `GET   /[toners|drums]` or `GET   /`

Returns an array of all registered toners, drums or items

* Response Body
  * `200` Array of requested items

#### Requesting `GET   /[toner|drum]/{id}`

Returns a single toner or drum item, including its complete transaction history

* Response Header
  * `Location` The URL of the newly created resource, if response code was 201

* Response Body
  * `200` Requested item including its complete transaction history
  * `404` Error message

#### Requesting `POST  /[toners|drums]`

Creates a new toner or drum item, returns said item on success

* Request Body  
  A valid JSON object, containing the following fields:
  * `name` _string_ The model name
  * `color` _integer_ The item's color, see Enums
  * `printer` _string_ The printer model to use this item in

* Response Header
  * `Location` The URL of the newly created resource, if response code was 201

* Response Body
  * `201` Newly created item
  * `400` Error message
  * `500` Error message

#### Requesting `POST  /[toner|drum]/{id}/transactions`

Deposit or withdraw a toner or drum, i.e. increase or decrease stock by 1

* Request Body  
  A valid JSON object, containing the following fields:
  * `action` _integer_ The transaction action, see Enums
  * `reason` _string_ The reason for this transaction

* Response Body
  * `204` Empty
  * `400` Error message
  * `403` Error message
  * `404` Error message
  * `409` Error message
  * `500` Error message

#### Requesting `PUT   /[toner|drum]/{id}`

Update a single item

* Request Body  
  A valid JSON object, containing a complete item

* Response Body
  * `200` Updated item
  * `400` Error message
  * `404` Error message
  * `500` Error message
