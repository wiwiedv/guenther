## Tonerliste API

### Conventions

#### Transaction Types
* `1` Toner deposited
* `2` Toner withdrawn


### Resources

#### `GET` /

>Retrieve a list of all registered toner models

* Request parameters
  * _none_
* Possible responses
  * `200` Array of toner objects
* Response headers
  * `X-Guenther-Version` Guenther version number


#### `GET` /{tonerId}

>Retrieve a specific toner model, including its transaction history

* Request parameters
  * _none_
* Possible responses
  * `200` Toner object including transaction history
  * `404` _String literal_ "Not found"
* Response headers
  * `X-Guenther-Version` Guenther version number


#### `POST` /

>Add a new toner model with initial stock 0

* Request parameters
  * `body` __model__ The name of the toner model, _mandatory_
* Possible responses
  * `201` Newly created toner object
  * `400` _String literal_ "Missing parameter 'model'"
  * `500` _String literal_ "Could not store data"
* Response headers
  * `X-Guenther-Version` Guenther version number
  * `Location` The URL of the newly created resource, if response code was 201


#### `POST` /{tonerId}

>Deposit a toner for the specified model, i.e. increase stock by 1

* Request parameters
  * `body` __reason__ The reason for depositing, e.g. "refill" or "put back", _mandatory_
* Possible responses
  * `201` Updated toner object
  * `400` _String literal_ "Missing parameter 'reason'"
  * `404` _String literal_ "Not found"
* Response headers
  * `X-Guenther-Version` Guenther version number
  * `Location` The URL of the newly created resource, if response code was 201


#### `DELETE` /{tonerId}

>Withdraw toner of the specified model, i.e. decrease stock by 1

* Request parameters
  * `body` __reason__ The reason for withdrawing, e.g. "wiwi-edv-prt01" or "", _mandatory_
* Possible responses
  * `200` Updated toner object
  * `400` _String literal_ "Missing parameter 'reason'"
  * `404` _String literal_ "Not found"
  * `409` _String literal_ "Toner out of stock"
* Response headers
  * `X-Guenther-Version` Guenther version number
