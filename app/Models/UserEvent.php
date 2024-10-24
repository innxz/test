<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * public $userId
 */
final class UserEvent extends Model
{
    protected $table = 'users_events';

    protected $fillable = [
        'user_id',
        'event_id',
    ];
}
