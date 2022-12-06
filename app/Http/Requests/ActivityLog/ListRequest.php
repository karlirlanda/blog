<?php

namespace App\Http\Requests\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
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
            'search'    => 'nullable|string|max:24',
            'status'    => 'nullable|in:0,1,2,3',
            'limit'     => 'integer|digits_between:1,50',
            'sort_column' => 'nullable',
            'sort_order' => 'in:asc,desc',
        ];
    }
}
