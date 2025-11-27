<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProcessoHabitacional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProcessoHabitacionalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_corretor_can_only_see_their_own_processos()
    {
        $corretor1 = User::factory()->create(['role' => 'corretor']);
        $corretor2 = User::factory()->create(['role' => 'corretor']);
        $cliente = User::factory()->create(['role' => 'cliente']);

        ProcessoHabitacional::factory()->create(['corretor_id' => $corretor1->id, 'cliente_id' => $cliente->id]);
        ProcessoHabitacional::factory()->create(['corretor_id' => $corretor2->id, 'cliente_id' => $cliente->id]);

        $token = JWTAuth::fromUser($corretor1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/processos');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_cliente_can_only_see_their_own_processos()
    {
        $corretor = User::factory()->create(['role' => 'corretor']);
        $cliente1 = User::factory()->create(['role' => 'cliente']);
        $cliente2 = User::factory()->create(['role' => 'cliente']);

        ProcessoHabitacional::factory()->create(['corretor_id' => $corretor->id, 'cliente_id' => $cliente1->id]);
        ProcessoHabitacional::factory()->create(['corretor_id' => $corretor->id, 'cliente_id' => $cliente2->id]);

        $token = JWTAuth::fromUser($cliente1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/processos');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_admin_can_see_all_processos()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $corretor = User::factory()->create(['role' => 'corretor']);
        $cliente = User::factory()->create(['role' => 'cliente']);

        ProcessoHabitacional::factory()->count(5)->create(['corretor_id' => $corretor->id, 'cliente_id' => $cliente->id]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/processos');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }
}
