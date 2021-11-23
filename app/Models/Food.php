<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
  use HasFactory;
  use SoftDeletes;

  protected $fillable = [
    'name', 'description', 'ingredients', 'price', 'rate', 'type', 'picture_path'
  ];

  public function getCreatedAtAttribute($value)
  {
    return Carbon::parse($value)->timestamp;
  }

  public function getUpdatedAtAttribute($value)
  {
    return Carbon::parse($value)->timestamp;
  }
}
