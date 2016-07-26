<?php

/*
 * This file is part of the Aureja package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aureja\Bundle\TestFrameworkBundle\Tests\Functional\Test;

use Aureja\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 7/26/16 8:23 PM
 */
class WebTestCaseTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
    }

    public function testDbIsolation()
    {

    }
}
 