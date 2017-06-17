[![Build Status](https://travis-ci.org/cloudcreativity/utils-collection.svg?branch=master)](https://travis-ci.org/cloudcreativity/utils-collection)

# cloudcreativity/utils-collection

Our standard collection class for handling lists in PHP. By lists, we mean
numerically indexed arrays.

This package also contains our `StandardIteratorInterface`. We use this if we
are writing specific collection classes that hold only particular types of
objects.

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.

## Contributing

File an issue, or ideally submit a pull request. Bug fixes should be submitted against the `master` branch,
new features/changes should be submitted against the `develop` branch. Pull requests should have updated or new
unit tests in them.

Make sure your IDE has an [EditorConfig](http://editorconfig.org) plugin installed.

## Testing

Clone the repository, then:

``` bash
composer install
bin/phpunit
```

## Collection

The `CloudCreativity\Utils\Collection\Collection` is a standard class for
handling numerically indexed lists. It comes with the following methods.

### Modifiers

The following methods modify the list contained within the collection:

* `add`
* `addMany`
* `addObject`
* `addObjects`
* `clear`
* `insertAt`
* `pop`
* `push`
* `pushMany`
* `pushObject`
* `pushObjects`
* `remove`
* `removeAt`
* `removeMany`
* `replace`
* `shift`
* `unshift`
* `unshiftMany`
* `unshiftObject`
* `unshiftObjects`

### Accessors

The following methods give access to items within the collection:

* `first`
* `find`
* `itemAt`
* `last`

### Querying

The following methods can be used to assess or query the contents of the
collection;

* `any`
* `contains`
* `equals`
* `every`
* `indexOf`
* `isEmpty`
* `isNotEmpty`
* `search`

### Helpers

The following methods assist with handling the list, and return new instances
of the collection. (I.e. the original collection is not modified.)

* `all`
* `cast` (static)
* `chunk`
* `compact`
* `copy`
* `count`
* `filter`
* `invoke`
* `itemsAt`
* `map`
* `pad`
* `reduce`
* `reject`
* `replicate`
* `reverse`
* `slice`
* `sort`
* `sync`
* `unique`
* `without`
