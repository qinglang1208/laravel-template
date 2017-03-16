<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Jobs\SyncUserFromQywx;
use Queue;

class UserTest extends TestCase
{
    public function testList()
    {
        $this->getJson('/api/users/list')->assertStatus(401);

        $this->iam('usertest')->getJson('/api/users/list')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users/list')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function testSync()
    {
        $this->postJson('/api/users/sync')->assertStatus(401);

        $this->iam('usertest')->postJson('/api/users/sync')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users/sync')->assertStatus(405);

        $this->iam('useradmin', function () {
            Queue::fake();
        })->postJson('/api/users/sync')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        Queue::assertPushed(SyncUserFromQywx::class);
    }

    public function testAllroles()
    {
        $this->getJson('/api/users/allroles')->assertStatus(401);

        $this->iam('usertest')->getJson('/api/users/allroles')->assertStatus(403);

        $this->iam('useradmin')
            ->getJson('/api/users/allroles')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function testAttachroles()
    {
        $this->postJson('/api/users/attachroles')->assertStatus(401);

        $this->iam('usertest')->postJson('/api/users/attachroles')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users/attachroles')->assertStatus(405);

        $this->iam('useradmin')
            ->postJson('/api/users/attachroles', ['username' => 'usertest', 'rolenames' => ['admin', 'xxxx']])
            ->assertStatus(200)
            ->assertJson(['status' => 'error']);

        $this->iam('useradmin')
            ->postJson('/api/users/attachroles', ['username' => 'usertest', 'rolenames' => ['admin', 'user']])
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }
}
