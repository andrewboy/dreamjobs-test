<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Purify;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'completed',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->name = Purify::clean($model->name);
            $model->description = Purify::clean($model->description);
        });

        static::updating(function ($model) {
            $model->name = Purify::clean($model->name);
            $model->description = Purify::clean($model->description);
        });

        static::saving(function ($model) {
            $model->name = Purify::clean($model->name);
            $model->description = Purify::clean($model->description);
        });
    }
}
