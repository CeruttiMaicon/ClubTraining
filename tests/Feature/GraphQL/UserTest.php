<?php

namespace Tests\Feature\GraphQL;

use App\Models\Position;
use App\Models\Team;
use App\Models\User;
use Faker\Factory as Faker;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected $graphql = true;

    protected $tenancy = true;

    protected $otherUser = false;

    protected $login = true;

    private $role = 'technician';

    public static $data = [
        'id',
        'name',
        'email',
        'emailVerifiedAt',
        'createdAt',
        'updatedAt',
    ];

    private function setPermissions(bool $hasPermission)
    {
        $this->checkPermission($hasPermission, $this->role, 'edit-user');
        $this->checkPermission($hasPermission, $this->role, 'view-user');
    }

    /**
     * Listagem de todos os usuários.
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @dataProvider listProvider
     *
     * @return void
     */
    public function usersList(
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        User::factory()
            ->has(Position::factory()->count(3))
            ->create();

        $response = $this->graphQL(
            'users',
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
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'users' => [
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
                'typeMessageError' => 'message',
                'expectedMessage' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                ],
                'hasPermission' => false,
            ],
        ];
    }

    /**
     * Listagem de um usuário
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @dataProvider infoProvider
     *
     * @return void
     */
    public function userInfo(
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $user = User::factory()
            ->has(Position::factory()->count(3))
            ->create();
        $user->save();

        $response = $this->graphQL(
            'user',
            [
                'id' => $user->id,
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
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'user' => self::$data,
                    ],
                ],
                'hasPermission' => true,
            ],
            'without permission' => [
                'typeMessageError' => 'message',
                'expectedMessage' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                ],
                'hasPermission' => false,
            ],
        ];
    }

    /**
     * Método de criação de um usuário.
     *
     * @dataProvider userCreateProvider
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @return void
     */
    public function userCreate(
        $parameters,
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasTeam,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        if ($hasTeam) {
            $team = Team::factory()->create();
            $parameters['teamId'] = $team->id;
        }

        if ($parameters['name'] == null) {
            $parameters['name'] = ' ';
        }

        $response = $this->graphQL(
            'userCreate',
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
    public static function userCreateProvider()
    {
        $faker = Faker::create();
        $emailExistent = $faker->email;
        $cpfExistent = strval($faker->numberBetween(10000000000, 99999999999));
        $rgExistent = strval($faker->numberBetween(10000000000, 99999999999));

        $password = env('PASSWORD_TEST', '123456');

        $userCreate = ['userCreate'];

        return [
            'create user with teams, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'roleId' => [2],
                    'positionId' => [1],
                    'teamId' => [1],
                    'positionId' => [1],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => true,
                'hasPermission' => true,
            ],
            'create user with position, success' => [
                [
                    'name' => $faker->name,
                    'email' => $emailExistent,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'positionId' => [1],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with non-mandatory parameters, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'cpf' => $cpfExistent,
                    'phone' => $faker->phoneNumber,
                    'rg' => $rgExistent,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'positionId' => [1],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with cpf existent, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'cpf' => $cpfExistent,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [3],
                    'password' => $password,
                ],
                'typeMessageError' => 'cpf',
                'expectedMessage' => 'UserCreate.cpf_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with rg existent, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'rg' => $rgExistent,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [3],
                    'password' => $password,
                ],
                'typeMessageError' => 'rg',
                'expectedMessage' => 'UserCreate.rg_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'declare roleId is required, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [],
                ],
                'typeMessageError' => 'roleId',
                'expectedMessage' => 'UserCreate.role_id_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [3],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with birth date, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [3],
                    'password' => $password,
                    'birthDate' => $faker->date(),
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with 2 roles, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [3],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user with permission that shouldnt have, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [1],
                    'password' => $password,
                ],
                'typeMessageError' => 'roleId',
                'expectedMessage' => 'PermissionAssignment.validation_message_error',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'create user without permission, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'password' => $password,
                ],
                'typeMessageError' => 'message',
                'expectedMessage' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => false,
            ],
            'text password less than 6 characters, expected error' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'password' => '12345',
                ],
                'typeMessageError' => 'password',
                'expectedMessage' => 'UserCreate.password_min_6',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'text password with 6 characters, success' => [
                [
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'password' => $password,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userCreate' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is required, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'email' => ' ',
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserCreate.email_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'name field is required, expected error' => [
                [
                    'name' => null,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'email' => $faker->email,
                ],
                'typeMessageError' => 'name',
                'expectedMessage' => 'UserCreate.name_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'name field is min 3 characters, expected error' => [
                [
                    'name' => 'Th',
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'email' => $faker->email,
                ],
                'typeMessageError' => 'name',
                'expectedMessage' => 'UserCreate.name_min_3',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is not unique, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'email' => $emailExistent,
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserCreate.email_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is not email valid, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'email' => 'notemail.com',
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserCreate.email_is_valid',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userCreate,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Método de edição de um usuário.
     *
     * @dataProvider userEditProvider
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @return void
     */
    public function userEdit(
        $parameters,
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasTeam,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        $user = User::find($this->user->id);

        if (isset($parameters['cpf']) && $parameters['cpf']) {
            $parameters['cpf'] = User::factory()->create()->information->cpf;
        } elseif (isset($parameters['cpf']) && !$parameters['cpf']) {
            $faker = Faker::create();
            $parameters['cpf'] = (string) $faker->numberBetween(10000000000, 99999999999);
        }

        if (isset($parameters['rg']) && $parameters['rg']) {
            $parameters['rg'] = User::factory()->create()->information->rg;
        } elseif (isset($parameters['rg']) && !$parameters['rg']) {
            $faker = Faker::create();
            $parameters['rg'] = (string) $faker->numberBetween(100000000, 999999999);
        }

        if ($hasTeam) {
            $team = Team::factory()->create();
            $parameters['teamId'] = $team->id;
        }

        $parameters['id'] = $user->id;

        if ($expectedMessage == 'UserEdit.email_is_valid') {
            $parameters['email'] = 'notemail.com';
        } elseif ($expectedMessage != 'UserEdit.email_required') {
            $parameters['email'] = $this->email;
        } else {
            $parameters['email'] = ' ';
        }

        if ($expectedMessage == 'UserEdit.email_unique') {
            $userExist = User::factory()
                ->has(Position::factory()->count(3))
                ->create();

            $parameters['email'] = $userExist->email;
        }

        $response = $this->graphQL(
            'userEdit',
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
    public static function userEditProvider()
    {
        $faker = Faker::create();

        $password = env('PASSWORD_TEST', '123456');
        $userEdit = ['userEdit'];

        return [
            'declare roleId is required, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [],
                ],
                'typeMessageError' => 'roleId',
                'expectedMessage' => 'UserEdit.role_id_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user with permission that shouldnt have, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [1],
                ],
                'typeMessageError' => 'roleId',
                'expectedMessage' => 'PermissionAssignment.validation_message_error',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user without permission, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'message',
                'expectedMessage' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => false,
            ],
            'edit user with cpf, rg and phone, success' => [
                [
                    'name' => $faker->name,
                    'cpf' => false,
                    'rg' => false,
                    'phone' => $faker->phoneNumber,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => true,
                'hasPermission' => true,
            ],
            'edit user with cpf not unique, expected error' => [
                [
                    'name' => $faker->name,
                    'cpf' => true,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'cpf',
                'expectedMessage' => 'UserEdit.cpf_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user with rg not unique, expected error' => [
                [
                    'name' => $faker->name,
                    'rg' => true,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'rg',
                'expectedMessage' => 'UserEdit.rg_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user with team, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                    'positionId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => true,
                'hasPermission' => true,
            ],
            'edit user with position, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'roleId' => [2],
                    'positionId' => [1],
                    'teamId' => [1],
                    'positionId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user with birth date, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'birthDate' => $faker->date(),
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'edit user with 2 roles, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2, 3],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'text password less than 6 characters, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => '12345',
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'password',
                'expectedMessage' => 'UserEdit.password_min_6',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'text password with 6 characters, success' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'text password not send, expected success' => [
                [
                    'name' => $faker->name,
                    'password' => null,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userEdit' => self::$data,
                    ],
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is required, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserEdit.email_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'name field is required, expected error' => [
                [
                    'name' => ' ',
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'name',
                'expectedMessage' => 'UserEdit.name_required',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'name field is min 3 characters, expected error' => [
                [
                    'name' => 'Th',
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'name',
                'expectedMessage' => 'UserEdit.name_min_3',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is not unique, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserEdit.email_unique',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
            'email field is not email valid, expected error' => [
                [
                    'name' => $faker->name,
                    'password' => $password,
                    'email' => 'notemail.com',
                    'positionId' => [1],
                    'teamId' => [1],
                    'roleId' => [2],
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserEdit.email_is_valid',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userEdit,
                ],
                'hasTeam' => false,
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Método de deletar um usuário.
     *
     * @dataProvider userDeleteProvider
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @return void
     */
    public function deleteUser($data, $typeMessageError, $expectedMessage, $expected, $hasPermission)
    {
        $this->setPermissions($hasPermission);

        $user = User::factory()
            ->has(Position::factory()->count(3))
            ->create();

        $parameters['id'] = $user->id;

        if ($data['error'] != null) {
            $parameters['id'] = $data['error'];
        }

        if ($expectedMessage == 'UserDelete.cannot_delete_own_account') {
            $parameters['id'] = $this->user->id;
        }

        if ($expectedMessage == 'UserDelete.ids_exists') {
            $parameters['id'] = User::max('id') + 1;
        }

        $response = $this->graphQL(
            'userDelete',
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
    public static function userDeleteProvider()
    {
        $userDelete = ['userDelete'];

        return [
            'delete a user, success' => [
                [
                    'error' => null,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => $userDelete,
                ],
                'hasPermission' => true,
            ],
            'delete user without permission, expected error' => [
                [
                    'error' => null,
                ],
                'typeMessageError' => 'message',
                'expectedMessage' => self::$unauthorized,
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userDelete,
                ],
                'hasPermission' => false,
            ],
            'delete user that does not exist, expected error' => [
                [
                    'error' => 9999,
                ],
                'typeMessageError' => 'id',
                'expectedMessage' => 'UserDelete.ids_exists',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userDelete,
                ],
                'hasPermission' => true,
            ],
            'delete user can not delete own account, expected error' => [
                [
                    'error' => 'this',
                ],
                'typeMessageError' => 'id',
                'expectedMessage' => 'UserDelete.cannot_delete_own_account',
                'expected' => [
                    'errors' => self::$errors,
                    'data' => $userDelete,
                ],
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Listar informações de usuário logado.
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @dataProvider meProvider
     *
     * @return void
     */
    public function me(
        $typeMessageError,
        $expectedMessage,
        $expected,
        bool $hasPermission
    ) {
        $this->setPermissions($hasPermission);

        User::factory()
            ->has(Position::factory()->count(3))
            ->has(Team::factory()->count(3))
            ->create();

        $response = $this->graphQL(
            'me',
            [
            ],
            [
                'id',
                'name',
                'email',
                'positions' => [
                    'name',
                ],
                'teams' => [
                    'name',
                ],
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
    public static function meProvider()
    {
        return [
            'with auth' => [
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'me' => [
                            'id',
                            'name',
                            'email',
                            'positions',
                            'teams',
                        ],
                    ],
                ],
                'hasPermission' => true,
            ],
        ];
    }

    /**
     * Método de criar senha para um usuário.
     *
     * @dataProvider setPasswordProvider
     *
     * @author Maicon Cerutti
     *
     * @test
     *
     * @return void
     */
    public function setPassword($data, $typeMessageError, $expectedMessage, $expected, $hasPermission)
    {
        $this->setPermissions($hasPermission);

        $user = User::factory()
            ->has(Position::factory()->count(3))
            ->create();

        if ($data['email']) {
            $parameters['email'] = $user->email;
        }
        if ($data['email'] === 'not_valid') {
            $parameters['email'] = 'notemail.com';
        }
        if ($data['token']) {
            $parameters['token'] = $user->set_password_token;
        }
        if ($data['token'] === 'not_find_user_invalid_token') {
            $parameters['token'] = 'not_find_user_invalid_token';
        }
        if ($data['password']) {
            $parameters['password'] = env('PASSWORD_TEST', '1234');
        }
        if ($data['password'] === 'min_6') {
            $parameters['password'] = '1234';
        }
        if ($data['passwordConfirmation']) {
            $parameters['passwordConfirmation'] = env('PASSWORD_TEST', '1234');
        }
        if ($data['passwordConfirmation'] === 'not_match') {
            $parameters['passwordConfirmation'] = '12345678';
        }

        $response = $this->graphQL(
            'userSetPassword',
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
    public static function setPasswordProvider()
    {
        $userSetPassword = ['userSetPassword'];

        return [
            'set password a user, success' => [
                [
                    'error' => null,
                    'email' => true,
                    'token' => true,
                    'password' => true,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => false,
                'expectedMessage' => false,
                'expected' => [
                    'data' => [
                        'userSetPassword' => [
                            'id',
                            'name',
                            'email',
                            'emailVerifiedAt',
                            'createdAt',
                            'updatedAt',
                        ],
                    ],
                ],
                'hasPermission' => true,
            ],
            'set password a user, not send email, error' => [
                [
                    'error' => true,
                    'email' => false,
                    'token' => true,
                    'password' => true,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserSetPassword.email_required',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, not valid email, error' => [
                [
                    'error' => true,
                    'email' => 'not_valid',
                    'token' => true,
                    'password' => true,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'email',
                'expectedMessage' => 'UserSetPassword.email_is_valid',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, not send token, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => false,
                    'password' => true,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'token',
                'expectedMessage' => 'UserSetPassword.token_required',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, not token string, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => 'not_find_user_invalid_token',
                    'password' => true,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'token',
                'expectedMessage' => 'UserSetPassword.token_exists',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, not send password, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => true,
                    'password' => false,
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'password',
                'expectedMessage' => 'UserSetPassword.password_required',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, send password min 6 characters, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => true,
                    'password' => 'min_6',
                    'passwordConfirmation' => true,
                ],
                'typeMessageError' => 'password',
                'expectedMessage' => 'UserSetPassword.password_min_6',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, send password does not match, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => true,
                    'password' => true,
                    'passwordConfirmation' => 'not_match',
                ],
                'typeMessageError' => 'passwordConfirmation',
                'expectedMessage' => 'UserSetPassword.password_confirmation_same',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
            'set password a user, not send passwordConfirmation, error' => [
                [
                    'error' => true,
                    'email' => true,
                    'token' => true,
                    'password' => true,
                    'passwordConfirmation' => false,
                ],
                'typeMessageError' => 'passwordConfirmation',
                'expectedMessage' => 'UserSetPassword.password_confirmation_required',
                'expected' => [
                    'data' => $userSetPassword,
                ],
                'hasPermission' => true,
            ],
        ];
    }
}
