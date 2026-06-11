<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'result',
        'label',
        'image',
        'user_id',
        'cnn_accuracy',
        'cnn_auc',
        'cnn_label',
        'vit_accuracy',
        'vit_auc',
        'vit_label',
        'validation_doctor',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'cnn_accuracy' => 'float',
        'cnn_auc' => 'float',
        'vit_accuracy' => 'float',
        'vit_auc' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}