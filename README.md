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
Optionally, add the header 'Accept: application/json', so the respons is ALWAYS in JSON, even if fatal errors occur

## Testing

Before testing, make sure the ` $baseUrl` in tests/TestCase.php is correctly set according to your environment.
Run the tests:
```Shell
phpunit
```
