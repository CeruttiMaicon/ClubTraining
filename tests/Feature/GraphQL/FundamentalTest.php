<?php

namespace Tests\Feature\GraphQL;

use App\Models\Fundamental;
use Faker\Factory as Faker;
use Tests\TestCase;

class FundamentalTest extends TestCase
{
    protected $graphql = true;

    protected $tenancy = true;

    protected $login = true;

    private $role = 'technician';

    public static $data = [
        'id',
        'name',
        'userId',
        'createdAt',
        'updatedAt',
    ];

    private function setPermissions(bool $hasPermission)
    {
        $this->checkPermission($hasPermission, $this->role, 'edit-fundamental');
        $this->checkPermission($hasPermission, $this->role, 'view-fundamental');
    }

    /**
     * Listagem de todos os fundamentos.
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @dataProvider listProvider
     *
     * @return void
     */
    public function fundamentalsList(
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        Fundamental::factory()->make()->save();

        $response = $this->graphQL(
            'fundamentals',
            [
                'first' => 10,
                'page' => 1,
            ],
            [
                'paginatorInfo' => self::$paginatorInfo,
                'data' => self::$data,
            ],
            'query',
            false
        );

        $this->assertMessageError(
            $typeMessageError,
            $response,
            $hasPermission,
            $expectedMessage
        );

        if ($hasPermission) {
            $response
                ->assertJsonStructure($expected)
                ->assertStatus(200);
        }
    }

    /**
     * @return array
     */
    public static function listProvider()
    {
        return [
            'with permission' => [
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'data' => [
                        'fundamentals' => [
                            'paginatorInfo' => self::$paginatorInfo,
                            'data' => [
                                '*' => self::$data,
                            ],
                        ],
                    ],
                ],
                'hasPermission' => true,
            ],
            'without permission' => [
                'type_message_error' => 'message',
                'expected_message' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                ],
                'hasPermission' => false,
            ],
        ];
    }

    /**
     * Listagem de um fundamento
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @dataProvider infoProvider
     *
     * @return void
     */
    public function fundamentalInfo(
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $fundamental = Fundamental::factory()->make();
        $fundamental->save();

        $response = $this->graphQL(
            'fundamental',
            [
                'id' => $fundamental->id,
            ],
            self::$data,
            'query',
            false
        );

        $this->assertMessageError(
            $typeMessageError,
            $response,
            $hasPermission,
            $expectedMessage
        );

        if ($hasPermission) {
            $response->assertJsonStructure($expected)
                ->assertStatus(200);
        }
    }

    /**
     * @return array
     */
    public static function infoProvider()
    {
        return [
            'with permission' => [
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'data' => [
                        'fundamental' => self::$data,
                    ],
                ],
                'hasPermission' => true,
            ],
            'without permission' => [
                'type_message_error' => 'message',
                'expected_message' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                ],
                'hasPermission' => false,
            ],
        ];
    }

    /**
     * Método de criação de um fundamento.
     *
     * @dataProvider fundamentalCreateProvider
     *
     * @author Maicon Cerutti
     *
     * @return void
     */
    public function fundamentalCreate(
        $parameters,
        $typeMessageError,
        $expectedMessage,
        $expected,
        $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $response = $this->graphQL(
            'fundamentalCreate',
            $parameters,
            self::$data,
            'mutation',
            false,
            true
        );

        $this->assertMessageError($typeMessageError, $response, $hasPermission, $expectedMessage);

        $response
            ->assertJsonStructure($expected)
            ->assertStatus(200);
    }

    /**
     * @return array
     */
    public static function fundamentalCreateProvider()
    {
        $faker = Faker::create();
        $userId = 1;
        $nameExistent = $faker->name;
        $fundamentalCreate = ['fundamentalCreate'];

        return [
            'create fundamental, success' => [
                [
                    'name' => $nameExistent,
                    'userId' => $userId,
                ],
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'data' => [
                        'fundamentalCreate' => self::$data,
                    ],
                ],
                'hasPermission' => true,
            ],
            'create fundamental without permission, expected error' => [
                [
                    'name' => $faker->name,
                    'userId' => $userId,
                ],
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalCreate,
                ],
                'hasPermission' => false,
            ],
            'name field is not unique, expected error' => [
                [
                    'name' => $nameExistent,
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalCreate.name_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalCreate,
                ],
                'hasPermission' => true,
            ],
            'name field is required, expected error' => [
                [
                    'name' => ' ',
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalCreate.name_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalCreate,
                ],
                'hasPermission' => true,
            ],
            'name field is min 3 characteres, expected error' => [
                [
                    'name' => 'AB',
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalCreate.name_min',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalCreate,
                ],
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Método de edição de um fundamento.
     *
     * @dataProvider fundamentalEditProvider
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @return void
     */
    public function fundamentalEdit(
        $parameters,
        $typeMessageError,
        $expectedMessage,
        $expected,
        $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $fundamentalExist = Fundamental::factory()->make();
        $fundamentalExist->save();
        $fundamental = Fundamental::factory()->make();
        $fundamental->save();

        $parameters['id'] = $fundamental->id;

        if ($expectedMessage == 'FundamentalEdit.name_unique') {
            $parameters['name'] = $fundamentalExist->name;
        }

        $response = $this->graphQL(
            'fundamentalEdit',
            $parameters,
            self::$data,
            'mutation',
            false,
            true
        );

        $this->assertMessageError($typeMessageError, $response, $hasPermission, $expectedMessage);

        $response
            ->assertJsonStructure($expected)
            ->assertStatus(200);
    }

    /**
     * @return array
     */
    public static function fundamentalEditProvider()
    {
        $faker = Faker::create();
        $userId = 2;
        $fundamentalEdit = ['fundamentalEdit'];

        return [
            'edit fundamental without permission, expected error' => [
                [
                    'name' => $faker->name,
                    'userId' => $userId,
                ],
                'type_message_error' => 'message',
                'expected_message' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalEdit,
                ],
                'hasPermission' => false,
            ],
            'edit fundamental, success' => [
                [
                    'name' => $faker->name,
                    'userId' => $userId,
                ],
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'data' => [
                        'fundamentalEdit' => self::$data,
                    ],
                ],
                'hasPermission' => true,
            ],
            'name field is not unique, expected error' => [
                [
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalEdit.name_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalEdit,
                ],
                'hasPermission' => true,
            ],
            'name field is required, expected error' => [
                [
                    'name' => ' ',
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalEdit.name_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalEdit,
                ],
                'hasPermission' => true,
            ],
            'name field is min 3 characteres, expected error' => [
                [
                    'name' => 'AB',
                    'userId' => $userId,
                ],
                'type_message_error' => 'name',
                'expected_message' => 'FundamentalEdit.name_min',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalEdit,
                ],
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Método de exclusão de um fundamento.
     *
     * @author Maicon Cerutti
     *
     * @dataProvider fundamentalDeleteProvider
     *
     * @test
     *
     * @return void
     */
    public function fundamentalDelete(
        $data,
        $typeMessageError,
        $expectedMessage,
        $expected,
        $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $fundamental = Fundamental::factory()->make();
        $fundamental->save();

        $parameters['id'] = $fundamental->id;

        if ($data['error'] != null) {
            $parameters['id'] = $data['error'];
        }

        $response = $this->graphQL(
            'fundamentalDelete',
            $parameters,
            self::$data,
            'mutation',
            false,
            true
        );

        $this->assertMessageError($typeMessageError, $response, $hasPermission, $expectedMessage);

        $response
            ->assertJsonStructure($expected)
            ->assertStatus(200);
    }

    /**
     * @author Maicon Cerutti
     *
     * @return array
     */
    public static function fundamentalDeleteProvider()
    {
        $fundamentalDelete = ['fundamentalDelete'];

        return [
            'delete fundamental, success' => [
                [
                    'error' => null,
                ],
                'type_message_error' => false,
                'expected_message' => false,
                'expected' => [
                    'data' => [
                        'fundamentalDelete' => [self::$data],
                    ],
                ],
                'hasPermission' => true,
            ],
            'delete fundamental without permission, expected error' => [
                [
                    'error' => null,
                ],
                'type_message_error' => 'message',
                'expected_message' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalDelete,
                ],
                'hasPermission' => false,
            ],
            'delete fundamental that does not exist, expected error' => [
                [
                    'error' => 9999,
                ],
                'type_message_error' => 'message',
                'expected_message' => 'internal',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $fundamentalDelete,
                ],
                'hasPermission' => true,
            ],
        ];
    }
}
