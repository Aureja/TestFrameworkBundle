# TestFrameworkBundle

[![Build Status](https://travis-ci.org/Aureja/TestFrameworkBundle.svg?branch=master)](https://travis-ci.org/Aureja/TestFrameworkBundle)

This Bundle provides base classes for functional tests to assist in setting up test-databases.

## Installation

**Step 1**. Install via [Composer](https://getcomposer.org/)

```
composer require aureja/test-framework-bundle "dev-master"
```

**Step 2**. Add to `AppKernel.php`

```php
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
             // ...
             new Aureja\Bundle\TestFrameworkBundle\AurejaTestFrameworkBundle(),
             // ...
        ];
    }
}
```

**Step 3**. Basic usage

```php
<?php

use Aureja\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class AcmeTest extends WebTestCase
{
    // Tests
}
```

or

```php
<?php

use Aureja\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class AcmeTest extends WebTestCase
{
    // Tests
}
```
