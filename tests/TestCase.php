<?php

namespace Tests;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
//    use DatabaseTransactions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleEnum::EDITOR->value]);
        Role::create(['name' => RoleEnum::ADMIN->value]);

        Http::preventStrayRequests();
    }
}
