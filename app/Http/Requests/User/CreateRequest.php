<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|string|max:250',
            'username' => 'required|string|max:250',
            'password' => 'required|confirmed|min:6',
            'role_id' => 'required|exists:roles,id',
            'type' => 'required|integer|in:1,2,3'

        ];
    }
}
