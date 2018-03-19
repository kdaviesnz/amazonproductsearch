# amazonproductsearch

PHP Component to search Amazon for products.



## Install

Via Composer

``` bash
$ composer require kdaviesnz/amazonproductsearch
```

## Usage

``` php
Import the tables in amazon.sql into your database.

Edit src/config.php so that it uses your credentials.

To search for a specify product:

$result = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
	'B00136LUWW',
	"ASIN"
);
		
Similar products:
$similar_products = $result->frequently_bought_together();

Related products:
$related_products = $result->related_products( 'Tracks' );

Search by category:
$to = 1;
$result = \kdaviesnz\amazon\AmazonProductSearch::search(
	'cats',
	$to,
	\kdaviesnz\amazon\AmazonSort::sortByBest(),
	\kdaviesnz\amazon\AmazonFilter::noFilter(),
	'',
	'All'
);



## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) ) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- Kevin Davies 

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-downloads]: https://packagist.org/packages/:vendor/:package_name
[link-author]: https://github.com/kdaviesnz

# amazonproductsearch
