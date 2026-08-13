<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

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

        // Start a database transaction for each test
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback the transaction after each test
        DB::rollBack();

        parent::tearDown();
    }
}
