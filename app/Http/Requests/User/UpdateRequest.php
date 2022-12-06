<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(auth()->user()->type == 1 || auth()->user()->type == 2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id' => 'required|exists:users,id',
            'type' => 'integer|in:1,2,3',
            'username' => 'string|unique:users,username',
            'role' => 'integer',
            'name' => 'string|max:125|unique:users,name,' . $this->id,
            'password' => 'confirmed|min:6',
        ];
    }
}
