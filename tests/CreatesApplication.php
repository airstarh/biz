<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    use \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Your custom setup here if needed
    }
}
