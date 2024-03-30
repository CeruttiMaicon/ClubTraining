<?php

namespace Tests\Unit\App\GraphQL\Validators\Mutation;

use App\GraphQL\Validators\Mutation\ConfirmPresenceValidator;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Tests\TestCase;

class ConfirmPresenceValidatorTest extends TestCase
{
    /**
     * A basic unit test messages.
     *
     * @test
     *
     * @return void
     */
    public function messages()
    {
        $validator = new ConfirmPresenceValidator();

        $this->assertIsArray($validator->messages());
        $this->assertNotEmpty($validator->messages());
    }

    /**
     * A basic unit test rules.
     *
     * @test
     *
     * @return void
     */
    public function rules()
    {
        $validator = new ConfirmPresenceValidator();
        $validator->setArgs($this->mock(ArgumentSet::class, function (MockInterface $mock) {
            $mock->shouldReceive('toArray')->andReturn([
                'playerId' => 1,
                'trainingId' => 1,
            ]);
        }));

        $this->assertIsArray($validator->rules());
        $this->assertNotEmpty($validator->rules());
    }
}
