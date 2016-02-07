# JSON  reporter for Codeception
Codeception JSON  fromat reporter, can be used in parallel run.


JSON Reporter
========

This extension add another type of json report generator, since original one from PHPUnit is nor really suitble


## Installation

1. Install [Codeception](http://codeception.com) via Composer
2. Add  `zondor/codeception-json-reporter": "*"` to your `composer.json`
3. Run `composer install`.
4. Include extensions into `codeception.yml` configuration:

Sample:

```yaml
paths:
    tests: tests
    log: tests/_log
    data: tests/_data
    helpers: tests/_helpers
reporters:
    json: Codeception\Reporter\Json

```

Then can be run us ususal reporter
```bash
php ./vendor/bin/codeception run --json=report.json

```

-----
