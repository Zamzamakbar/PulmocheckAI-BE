<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PatientController
 */
final class PatientControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $patients = Patient::factory()->count(3)->create();

        $response = $this->get(route('patients.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PatientController::class,
            'store',
            \App\Http\Requests\PatientStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $result = $this->faker->word();
        $image = $this->faker->word();
        $user = User::factory()->create();

        $response = $this->post(route('patients.store'), [
            'name' => $name,
            'result' => $result,
            'image' => $image,
            'user_id' => $user->id,
        ]);

        $patients = Patient::query()
            ->where('name', $name)
            ->where('result', $result)
            ->where('image', $image)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $patients);
        $patient = $patients->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PatientController::class,
            'update',
            \App\Http\Requests\PatientUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $patient = Patient::factory()->create();
        $name = $this->faker->name();
        $result = $this->faker->word();
        $image = $this->faker->word();
        $user = User::factory()->create();

        $response = $this->put(route('patients.update', $patient), [
            'name' => $name,
            'result' => $result,
            'image' => $image,
            'user_id' => $user->id,
        ]);

        $patient->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $patient->name);
        $this->assertEquals($result, $patient->result);
        $this->assertEquals($image, $patient->image);
        $this->assertEquals($user->id, $patient->user_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->delete(route('patients.destroy', $patient));

        $response->assertNoContent();

        $this->assertModelMissing($patient);
    }
}
