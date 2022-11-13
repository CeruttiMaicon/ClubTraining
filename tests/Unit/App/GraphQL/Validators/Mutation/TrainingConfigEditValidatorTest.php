<?php

namespace Tests\Unit\App\GraphQL\Validators\Mutation;

use Tests\TestCase;
use App\GraphQL\Validators\Mutation\TrainingConfigEditValidator;

class TrainingConfigEditValidatorTest extends TestCase
{
    /**
     * A basic unit test messages.
     *
     * @return void
     */
    public function test_messages()
    {
        $validator = new TrainingConfigEditValidator();

        $this->assertIsArray($validator->messages());
        $this->assertNotEmpty($validator->messages());
    }
}