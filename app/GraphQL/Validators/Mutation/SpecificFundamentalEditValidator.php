<?php

namespace App\GraphQL\Validators\Mutation;

use Nuwave\Lighthouse\Validation\Validator;

final class SpecificFundamentalEditValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'unique:specific_fundamentals,name,' . $this->arg('id'),
                'required',
                'min:3',
            ],
            'userId' => [
                'required',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => trans('SpecificFundamentalEdit.name_unique'),
            'name.required' => trans('SpecificFundamentalEdit.name_required'),
            'name.min' => trans('SpecificFundamentalEdit.name_min'),
            'user_id.required' => trans('SpecificFundamentalEdit.user_id_required'),
        ];
    }
}
