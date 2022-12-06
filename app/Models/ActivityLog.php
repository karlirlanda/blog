<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    public function user()
    {
        return $this -> belongsTo(User::class());
    }

    protected $fillable = [
        'user_id',
        'user_type',
        'before_data', 
        'after_data',
        'action', 
        'table_name', 
        'table_id',
        'browser',
        'device',
        'platform' 
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array'
    ];
}
