# teamleader-coding-test

## Requirements

- PHP >= 5.6.26
- composer
- phpunit

## Installation

Once downloaded, navigate to the installation folder and install the dependencies using composer:
```Shell
cd teamleader-coding-test
composer install
```
Make sure the document root is set to the "public" folder

## Usage

Submit an order to /api/discount (POST request) using raw JSON in the request body.
Optionally, add the header 'Accept: application/json', so the respons is ALWAYS in JSON, even if fatal errors occur.

When a valid order is submitted, the response will be in JSON. It consists of:
- the original order, modified according to the discounts (if any)
- more info on the discounts (if any)

## Testing

Before testing, make sure the ` $baseUrl` in tests/TestCase.php is correctly set according to your environment.
Run the tests:
```Shell
phpunit
```
