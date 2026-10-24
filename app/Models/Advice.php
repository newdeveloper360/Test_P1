<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Advice extends Model {
    protected $table = 'advice';
    protected $fillable = ['subject', 'message', 'status', 'admin_note'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
