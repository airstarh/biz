<?php

namespace Tests;

trait CreatesApplication
{
    use \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpEnvironment();
    }

    protected function setUpEnvironment(): void
    {
        $this->setUpDatabase();
        $this->setUpCache();
    }

    protected function setUpDatabase(): void
    {
        $this->beforeApplicationCreated(function () {
            putenv('DB_CONNECTION=testing');
        });
    }

    protected function setUpCache(): void
    {
        $this->beforeApplicationCreated(function () {
            putenv('CACHE_DRIVER=array');
        });
    }
}
