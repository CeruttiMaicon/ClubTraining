<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserMutation
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function create($rootValue, array $args, GraphQLContext $context)
    {
        $args['password'] = Hash::make($args['password']);

        $user = \App\Models\User::create($args);

        return $user;
    }
}
