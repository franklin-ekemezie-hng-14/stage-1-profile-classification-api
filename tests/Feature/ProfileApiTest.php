<?php

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
});

function fakeProfileApis(
    string $name,
    array $genderize = [],
    array $agify = [],
    array $nationalize = [],
    int $genderizeStatus = 200,
    int $agifyStatus = 200,
    int $nationalizeStatus = 200,
): void {
    Http::fake([
        'https://api.genderize.io*' => Http::response(array_merge([
            'name' => $name,
            'gender' => 'female',
            'probability' => 0.99,
            'count' => 1234,
        ], $genderize), $genderizeStatus),
        'https://api.agify.io*' => Http::response(array_merge([
            'name' => $name,
            'age' => 46,
            'count' => 1234,
        ], $agify), $agifyStatus),
        'https://api.nationalize.io*' => Http::response(array_merge([
            'name' => $name,
            'country' => [
                [
                    'country_id' => 'US',
                    'probability' => 0.15,
                ],
                [
                    'country_id' => 'DRC',
                    'probability' => 0.85,
                ],
            ],
            'count' => 2,
        ], $nationalize), $nationalizeStatus),
    ]);
}

function assertUuidAndUtcTimestamp(array $profile): void
{
    expect($profile['id'])->toBeString()
        ->and(Str::isUuid($profile['id']))->toBeTrue()
        ->and($profile['created_at'])->toBeString()
        ->and($profile['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z$/')
        ->and(CarbonImmutable::parse($profile['created_at'])->getOffset())->toBe(0);
}

function insertProfile(array $overrides = []): array
{
    $createdAt = $overrides['created_at'] ?? CarbonImmutable::parse('2026-04-15T12:00:00Z');

    $uuid = (string) Str::uuid7();
    if (! empty($overrides['id'])) {
        $uuid = $overrides['id'];
        unset($overrides['id']);
    }

    $profile = array_merge([
        'uuid' => $uuid,
        'name' => 'ella',
        'gender' => 'female',
        'gender_probability' => 0.99,
        'sample_size' => 1234,
        'age' => 46,
        'age_group' => 'adult',
        'country_id' => 'DRC',
        'country_probability' => 0.85,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ], $overrides);

    DB::table('profiles')->insertGetId($profile);

    $profile['id'] = $uuid;

    return $profile;
}

it('creates a profile successfully, stores it, and returns the exact response shape', function () {
    fakeProfileApis('ella');

    $response = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    $response
        ->assertStatus(201)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'name',
                'gender',
                'gender_probability',
                'sample_size',
                'age',
                'age_group',
                'country_id',
                'country_probability',
                'created_at',
            ],
        ]);

    Http::assertSentCount(3);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.genderize.io') && str_contains($request->url(), 'name=ella'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.agify.io') && str_contains($request->url(), 'name=ella'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.nationalize.io') && str_contains($request->url(), 'name=ella'));

    $profile = $response->json('data');

    assertUuidAndUtcTimestamp($profile);

    $response->assertExactJson([
        'status' => 'success',
        'data' => [
            'id' => $profile['id'],
            'name' => 'ella',
            'gender' => 'female',
            'gender_probability' => 0.99,
            'sample_size' => 1234,
            'age' => 46,
            'age_group' => 'adult',
            'country_id' => 'DRC',
            'country_probability' => 0.85,
            'created_at' => $profile['created_at'],
        ],
    ]);

    expect($profile['gender_probability'])->toBeFloat()
        ->and($profile['sample_size'])->toBeInt()
        ->and($profile['age'])->toBeInt()
        ->and($profile['country_probability'])->toBeFloat();

    $this->assertDatabaseCount('profiles', 1);
    $this->assertDatabaseHas('profiles', [
        'uuid' => $profile['id'],
        'name' => 'ella',
        'gender' => 'female',
        'sample_size' => 1234,
        'age' => 46,
        'age_group' => 'adult',
        'country_id' => 'DRC',
    ]);
});

it('returns the existing profile for a duplicate name without creating a new record', function () {
    fakeProfileApis('ella');

    $firstResponse = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ])->assertStatus(201);

    $existingProfile = $firstResponse->json('data');

    Http::fake();

    $secondResponse = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    expect(in_array($secondResponse->status(), [200, 201], true))->toBeTrue();

    $secondResponse
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'success',
            'message' => 'Profile already exists',
            'data' => $existingProfile,
        ]);

    $this->assertDatabaseCount('profiles', 1);
});

it('maps nationalize data to the highest probability country', function () {
    fakeProfileApis('mila', nationalize: [
        'country' => [
            [
                'country_id' => 'NG',
                'probability' => 0.42,
            ],
            [
                'country_id' => 'CA',
                'probability' => 0.76,
            ],
            [
                'country_id' => 'GH',
                'probability' => 0.51,
            ],
        ],
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => 'mila',
    ]);

    $response->assertStatus(201);

    expect($response->json('data.country_id'))->toBe('CA')
        ->and($response->json('data.country_probability'))->toBe(0.76);

    $this->assertDatabaseHas('profiles', [
        'name' => 'mila',
        'country_id' => 'CA',
    ]);
});

it('assigns the correct age group for each agify boundary', function (string $name, int $age, string $expectedAgeGroup) {
    fakeProfileApis($name, agify: [
        'age' => $age,
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => $name,
    ]);

    $response->assertStatus(201);

    expect($response->json('data.age'))->toBe($age)
        ->and($response->json('data.age_group'))->toBe($expectedAgeGroup);

    $this->assertDatabaseHas('profiles', [
        'name' => $name,
        'age' => $age,
        'age_group' => $expectedAgeGroup,
    ]);
})->with([
    ['twelve', 12, 'child'],
    ['thirteen', 13, 'teenager'],
    ['nineteen', 19, 'teenager'],
    ['twenty', 20, 'adult'],
    ['fiftynine', 59, 'adult'],
    ['sixty', 60, 'senior'],
]);

it('returns a 400 error when the name field is missing', function () {
    $response = $this->postJson('/api/profiles', []);

    $response
        ->assertStatus(400)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Missing or empty name',
        ]);
});

it('returns a 400 error when the name field is empty', function () {
    $response = $this->postJson('/api/profiles', [
        'name' => '',
    ]);

    $response
        ->assertStatus(400)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Missing or empty name',
        ]);
});

it('returns a 422 error when the name field has an invalid type', function () {
    $response = $this->postJson('/api/profiles', [
        'name' => ['ella'],
    ]);

    $response
        ->assertStatus(422)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'message',
        ])
        ->assertJsonPath('status', 'error')
        ->assertJsonCount(2);

    expect($response->json('message'))->toBeString()->not->toBe('');
});

it('returns a 502 error when genderize returns a null gender and does not store the profile', function () {
    fakeProfileApis('ella', genderize: [
        'gender' => null,
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    $response
        ->assertStatus(502)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Genderize returned an invalid response',
        ]);

    $this->assertDatabaseCount('profiles', 0);
});

it('returns a 502 error when genderize returns a zero sample size and does not store the profile', function () {
    fakeProfileApis('ella', genderize: [
        'count' => 0,
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    $response
        ->assertStatus(502)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Genderize returned an invalid response',
        ]);

    $this->assertDatabaseCount('profiles', 0);
});

it('returns a 502 error when agify returns a null age and does not store the profile', function () {
    fakeProfileApis('ella', agify: [
        'age' => null,
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    $response
        ->assertStatus(502)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Agify returned an invalid response',
        ]);

    $this->assertDatabaseCount('profiles', 0);
});

it('returns a 502 error when nationalize returns no country data and does not store the profile', function () {
    fakeProfileApis('ella', nationalize: [
        'country' => [],
    ]);

    $response = $this->postJson('/api/profiles', [
        'name' => 'ella',
    ]);

    $response
        ->assertStatus(502)
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Nationalize returned an invalid response',
        ]);

    $this->assertDatabaseCount('profiles', 0);
});

it('returns a stored profile by id', function () {
    $profile = insertProfile([
        'name' => 'emmanuel',
        'gender' => 'male',
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
    ]);

    $response = $this->getJson("/api/profiles/{$profile['id']}");

    $response
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'name',
                'gender',
                'gender_probability',
                'sample_size',
                'age',
                'age_group',
                'country_id',
                'country_probability',
                'created_at',
            ],
        ]);

    $data = $response->json('data');

    assertUuidAndUtcTimestamp($data);

    $response->assertExactJson([
        'status' => 'success',
        'data' => [
            'id' => $profile['id'],
            'name' => 'emmanuel',
            'gender' => 'male',
            'gender_probability' => 0.99,
            'sample_size' => 1234,
            'age' => 25,
            'age_group' => 'adult',
            'country_id' => 'NG',
            'country_probability' => 0.85,
            'created_at' => $data['created_at'],
        ],
    ]);
});

it('returns a 404 error when the requested profile does not exist', function () {
    $response = $this->getJson('/api/profiles/'.Str::uuid()->toString());

    $response
        ->assertNotFound()
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'message',
        ])
        ->assertJsonPath('status', 'error')
        ->assertJsonCount(2);

    expect($response->json('message'))->toBeString()->not->toBe('');
});

it('returns all profiles with the exact list response shape', function () {
    $first = insertProfile([
        'id' => '0196354c-c51f-7b79-b5d0-72245f52f001',
        'name' => 'emmanuel',
        'gender' => 'male',
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
    ]);

    $second = insertProfile([
        'id' => '0196354c-c51f-7b79-b5d0-72245f52f002',
        'name' => 'sarah',
        'gender' => 'female',
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'US',
    ]);

    $response = $this->getJson('/api/profiles');

    $response
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'count',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'gender',
                    'age',
                    'age_group',
                    'country_id',
                ],
            ],
        ]);

    expect($response->json('status'))->toBe('success')
        ->and($response->json('count'))->toBe(2)
        ->and(collect($response->json('data'))->sortBy('id')->values()->all())
        ->toBe([
            [
                'id' => $first['id'],
                'name' => 'emmanuel',
                'gender' => 'male',
                'age' => 25,
                'age_group' => 'adult',
                'country_id' => 'NG',
            ],
            [
                'id' => $second['id'],
                'name' => 'sarah',
                'gender' => 'female',
                'age' => 28,
                'age_group' => 'adult',
                'country_id' => 'US',
            ],
        ]);
});

it('filters profiles by gender case-insensitively', function () {
    insertProfile([
        'name' => 'emmanuel',
        'gender' => 'male',
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
    ]);

    insertProfile([
        'name' => 'sarah',
        'gender' => 'female',
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'US',
    ]);

    $response = $this->getJson('/api/profiles?gender=Male');

    $response->assertOk();

    expect($response->json('count'))->toBe(1)
        ->and($response->json('data.0.name'))->toBe('emmanuel')
        ->and($response->json('data.0.gender'))->toBe('male');
});

it('filters profiles by country id case-insensitively', function () {
    insertProfile([
        'name' => 'emmanuel',
        'gender' => 'male',
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
    ]);

    insertProfile([
        'name' => 'sarah',
        'gender' => 'female',
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'US',
    ]);

    $response = $this->getJson('/api/profiles?country_id=ng');

    $response->assertOk();

    expect($response->json('count'))->toBe(1)
        ->and($response->json('data.0.name'))->toBe('emmanuel')
        ->and($response->json('data.0.country_id'))->toBe('NG');
});

it('filters profiles by age group case-insensitively', function () {
    insertProfile([
        'name' => 'timi',
        'gender' => 'male',
        'age' => 17,
        'age_group' => 'teenager',
        'country_id' => 'NG',
    ]);

    insertProfile([
        'name' => 'sarah',
        'gender' => 'female',
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'US',
    ]);

    $response = $this->getJson('/api/profiles?age_group=TEENAGER');

    $response->assertOk();

    expect($response->json('count'))->toBe(1)
        ->and($response->json('data.0.name'))->toBe('timi')
        ->and($response->json('data.0.age_group'))->toBe('teenager');
});

it('applies combined filters case-insensitively', function () {
    insertProfile([
        'name' => 'emmanuel',
        'gender' => 'male',
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
    ]);

    insertProfile([
        'name' => 'timi',
        'gender' => 'male',
        'age' => 17,
        'age_group' => 'teenager',
        'country_id' => 'NG',
    ]);

    insertProfile([
        'name' => 'sarah',
        'gender' => 'female',
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'US',
    ]);

    $response = $this->getJson('/api/profiles?gender=MALE&country_id=ng&age_group=ADULT');

    $response->assertOk();

    expect($response->json('count'))->toBe(1)
        ->and($response->json('data'))->toBe([
            [
                'id' => $response->json('data.0.id'),
                'name' => 'emmanuel',
                'gender' => 'male',
                'age' => 25,
                'age_group' => 'adult',
                'country_id' => 'NG',
            ],
        ])
        ->and(Str::isUuid($response->json('data.0.id')))->toBeTrue();

});

it('deletes a profile successfully and returns no content', function () {
    $profile = insertProfile([
        'name' => 'ella',
    ]);

    $this->assertDatabaseHas('profiles', [
        'uuid' => $profile['id'],
    ]);

    $response = $this->deleteJson("/api/profiles/{$profile['id']}");

    $response
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', '*');

    $this->assertDatabaseMissing('profiles', [
        'uuid' => $profile['id'],
    ]);
});

it('returns a 404 error when deleting a non-existent profile', function () {
    $response = $this->deleteJson('/api/profiles/'.Str::uuid()->toString());

    $response
        ->assertNotFound()
        ->assertHeader('Access-Control-Allow-Origin', '*')
        ->assertJsonStructure([
            'status',
            'message',
        ])
        ->assertJsonPath('status', 'error')
        ->assertJsonCount(2);

    expect($response->json('message'))->toBeString()->not->toBe('');
});
