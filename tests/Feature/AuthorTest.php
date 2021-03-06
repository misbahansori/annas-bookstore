<?php

namespace Tests\Feature;

use App\User;
use App\Author;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_returns_all_authors_as_a_collection_of_resource_objects()
    {
        Passport::actingAs(factory(User::class)->create());
        $authors = factory(Author::class, 2)->create();

        $this->getJson(route('authors.index'), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $authors[0]->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $authors[0]->name,
                            'created_at' => $authors[0]->created_at->toJson(),
                            'updated_at' => $authors[0]->updated_at->toJson(),
                        ]
                    ], [
                        'id' => $authors[1]->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $authors[1]->name,
                            'created_at' => $authors[1]->created_at->toJson(),
                            'updated_at' => $authors[1]->updated_at->toJson(),
                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function it_can_paginate_authors_through_a_page_query_parameter()
    {
        Passport::actingAs(factory(User::class)->create());
        $authors = factory(Author::class, 10)->create();

        $this->getJson(route('authors.index', ['page[size]' => 5, 'page[number]' => 1]), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $authors[0]->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $authors[0]->name,
                            'created_at' => $authors[0]->created_at->toJson(),
                            'updated_at' => $authors[0]->updated_at->toJson(),
                        ]
                    ], [
                        'id' => $authors[1]->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $authors[1]->name,
                            'created_at' => $authors[1]->created_at->toJson(),
                            'updated_at' => $authors[1]->updated_at->toJson(),
                        ]
                    ],
                ],
                'links' => [
                    'first' => route('authors.index', ['page[size]' => 5, 'page[number]' => 1]),
                    'last' => route('authors.index', ['page[size]' => 5, 'page[number]' => 2]),
                    'prev' => null,
                    'next' => route('authors.index', ['page[size]' => 5, 'page[number]' => 2])
                ]
            ]);
    }

    /** @test */
    public function it_can_sort_authors_by_name_through_a_sort_query_parameter()
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        [$bertram, $clause, $anna] = collect(['Bertram', 'Clause', 'Anna'])->map(function ($name) {
            return factory(Author::class)->create(['name' => $name]);
        });

        $this->getJson(route('authors.index', ['sort' => 'name']), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $anna->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $anna->name,
                        ]
                    ], [
                        'id' => $bertram->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $bertram->name,
                        ]
                    ], [
                        'id' => $clause->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $clause->name,
                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function it_can_sort_authors_by_name_in_descending_order_through_a_sort_query_paramaters()
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        [$bertram, $clause, $anna] = collect(['Bertram', 'Clause', 'Anna'])->map(function ($name) {
            return factory(Author::class)->create(['name' => $name]);
        });

        $this->getJson(route('authors.index', ['sort' => '-name']), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $clause->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $clause->name,
                        ]
                    ], [
                        'id' => $bertram->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $bertram->name,
                        ]
                    ], [
                        'id' => $anna->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $anna->name,

                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function it_can_sort_authors_by_multiple_attributes_through_a_sort_query_parameter()
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        $createdAt = now();

        [$bertram, $clause, $anna] = collect(['Bertram', 'Clause', 'Anna'])->map(function ($name) use ($createdAt) {
            $createdAt->addSecond(1);
            return factory(Author::class)->create(['name' => $name, 'created_at' => $createdAt]);
        });

        $this->getJson(route('authors.index', ['sort' => 'created_at,name']), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $bertram->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $bertram->name,
                        ]
                    ], [
                        'id' => $clause->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $clause->name,
                        ]
                    ], [
                        'id' => $anna->id,
                        'type' => 'authors',
                        'attributes' => [
                            'name' => $anna->name,

                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function it_returns_an_author_as_a_resource_object()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->getJson(route('authors.show', $author), [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $author->id,
                    'type' => 'authors',
                    'attributes' => [
                        'name' => $author->name,
                        'created_at' => $author->created_at->toJson(),
                        'updated_at' => $author->updated_at->toJson(),
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_author_from_a_resource_object()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => $name = $this->faker->name(),
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'id' => '1',
                    'type' => 'authors',
                    'attributes' => [
                        'name' => $name,
                        'created_at' => now()->setMilliseconds(0)->toJson(),
                        'updated_at' => now()->setMilliseconds(0)->toJson(),
                    ]
                ]
            ])
            ->assertHeader('Location', route('authors.show', 1));

        $this->assertDatabaseHas('authors', ['id' => 1, 'name' => $name]);
    }

    /** @test */
    public function it_validates_that_the_type_member_is_given_when_creating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => '',
                'attributes' => [
                    'name' => $name = $this->faker->name(),
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The data.type field is required.',
                        'source'  => [
                            'pointer' => '/data/type'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_that_the_type_member_has_the_value_of_authors_when_creating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'author',
                'attributes' => [
                    'name' => $name = $this->faker->name(),
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The selected data.type is invalid.',
                        'source'  => [
                            'pointer' => '/data/type'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_that_the_attributes_member_has_been_given_when_creating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'authors',
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The data.attributes field is required.',
                        'source'  => [
                            'pointer' => '/data/attributes'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_that_the_attributes_member_is_an_object_given_when_creating_an_array()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'authors',
                'attributes' => 'not an array'
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The data.attributes must be an array.',
                        'source'  => [
                            'pointer' => '/data/attributes'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_that_a_name_attribute_is_given_when_creating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => ''
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The data.attributes.name field is required.',
                        'source'  => [
                            'pointer' => '/data/attributes/name'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_that_a_name_attribute_is_a_string_when_creating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());

        $this->postJson(route('authors.store'), [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 47
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title'   => 'Validation Error',
                        'details' => 'The data.attributes.name must be a string.',
                        'source'  => [
                            'pointer' => '/data/attributes/name'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_update_an_author_from_a_resource_object()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => (string) $author->id,
                'type' => 'authors',
                'attributes' => [
                    'name' => $newName = $this->faker->name()
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $author->id,
                    'type' => 'authors',
                    'attributes' => [
                        'name' => $newName,
                        'created_at' => $author->created_at->toJson(),
                        'updated_at' => now()->setMilliseconds(0)->toJson(),
                    ]
                ]
            ]);
        $this->assertDatabaseMissing('authors', ['id' => 1, 'name' => $author->name]);
        $this->assertDatabaseHas('authors', ['id' => 1, 'name' => $newName]);
    }

    /** @test */
    public function it_validates_that_an_id_member_is_given_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => $this->faker->name()
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.id field is required.',
                        'source' => [
                            'pointer' => '/data/id'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_an_id_member_is_string_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => 1,
                'type' => 'authors',
                'attributes' => [
                    'name' => $this->faker->name()
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.id must be a string.',
                        'source' => [
                            'pointer' => '/data/id'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_a_type_member_is_given_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => '',
                'attributes' => [
                    'name' => $this->faker->name()
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.type field is required.',
                        'source' => [
                            'pointer' => '/data/type'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_a_type_member_has_value_of_authors_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => 'author',
                'attributes' => [
                    'name' => $this->faker->name()
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The selected data.type is invalid.',
                        'source' => [
                            'pointer' => '/data/type'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_attibutes_of_member_is_given_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => 'authors',
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.attributes field is required.',
                        'source' => [
                            'pointer' => '/data/attributes'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_attibutes_of_member_is_an_object_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => 'not an object'
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.attributes must be an array.',
                        'source' => [
                            'pointer' => '/data/attributes'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_validates_that_attibutes_name_is_required_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => [
                    'name' => ''
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.attributes.name field is required.',
                        'source' => [
                            'pointer' => '/data/attributes/name'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }


    /** @test */
    public function it_validates_that_attibutes_name_must_be_a_string_when_updating_an_author()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->patchJson(route('authors.update', $author), [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => [
                    'name' => 1
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json'
        ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Validation Error',
                        'details' =>  'The data.attributes.name must be a string.',
                        'source' => [
                            'pointer' => '/data/attributes/name'
                        ]
                    ]
                ]
            ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** @test */
    public function it_can_delete_an_author_through_a_delete_request()
    {
        Passport::actingAs(factory(User::class)->create());
        $author = factory(Author::class)->create();

        $this->deleteJson(route('authors.destroy', $author), [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])
            ->assertStatus(204);

        $this->assertDatabaseMissing('authors', ['name' => $author->name]);
    }
}
